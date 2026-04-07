<?php
/**
 * TriageAI — AI-powered medical triage service
 * Hagz Clinic System
 *
 * Sends patient symptoms to an AI API and returns:
 *   priority   : Critical | Medium | Routine
 *   specialty  : Arabic specialty name
 *   wait_time  : human-readable expected wait
 *   reasoning  : short Arabic explanation
 *   confidence : 0.0 – 1.0
 */

require_once __DIR__ . '/../config/ai.php';

class TriageAI
{
    /**
     * Classify patient symptoms using AI.
     *
     * @param array  $symptoms   e.g. ['ألم شديد في الصدر', 'صعوبة في التنفس']
     * @param int    $pain       Pain level 1–10
     * @param string $duration   duration text
     * @param array  $conditions Chronic conditions
     * @param string $notes      Free text notes
     * @return array ['priority','specialty','wait_time','reasoning','confidence','source']
     */
    public static function classify(
        array  $symptoms,
        int    $pain,
        string $duration,
        array  $conditions = [],
        string $notes = ''
    ): array {
        $prompt = self::buildPrompt($symptoms, $pain, $duration, $conditions, $notes);

        try {
            $result = match (AI_PROVIDER) {
                'gemini' => self::callGemini($prompt),
                'groq'   => self::callGroq($prompt),
                'openai' => self::callOpenAI($prompt),
                default  => null,
            };

            if ($result && isset($result['priority'])) {
                $result['source'] = 'ai';
                return self::sanitize($result);
            }
        } catch (\Throwable $e) {
            error_log('[TriageAI] API failed: ' . $e->getMessage());
        }

        // Fallback: local rule-based engine
        return self::localFallback($symptoms, $pain, $duration, $conditions);
    }

    // ─────────────────────────────────────────────────────────────────
    private static function buildPrompt(
        array $symptoms, int $pain, string $duration, array $conditions, string $notes
    ): string {
        $sympList  = implode('، ', $symptoms) ?: 'لا أعراض محددة';
        $condList  = implode('، ', $conditions) ?: 'لا يوجد';

        return <<<PROMPT
أنت نظام ذكاء اصطناعي طبي متخصص في فرز المرضى (Medical Triage).
حلل البيانات التالية وصنّف الحالة:

الأعراض: {$sympList}
شدة الألم: {$pain}/10
مدة الأعراض: {$duration}
الحالات المزمنة: {$condList}
ملاحظات إضافية: {$notes}

اختر التخصص الطبي المناسب من القائمة التالية فقط:
طب عام | طب طوارئ | طب باطني | جراحة | أطفال | عظام | أعصاب | نساء وولادة

أجب بـ JSON فقط بدون أي نص إضافي:
{
  "priority": "Critical | Medium | Routine",
  "specialty": "اسم التخصص من القائمة أعلاه",
  "wait_time": "وصف مختصر لوقت الانتظار",
  "reasoning": "سبب التصنيف في جملة واحدة بالعربية",
  "confidence": 0.95
}
PROMPT;
    }

    // ─────────────────────────────────────────────────────────────────
    private static function callGemini(string $prompt): ?array
    {
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/'
            . AI_MODEL . ':generateContent?key=' . AI_API_KEY;

        $body = json_encode([
            'contents' => [['parts' => [['text' => $prompt]]]],
            'generationConfig' => [
                'temperature'     => 0.1,
                'maxOutputTokens' => 512,
            ],
        ]);

        $response = self::httpPost($url, $body, ['Content-Type: application/json']);
        $data     = json_decode($response, true);

        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
        if (!$text) return null;

        // Strip markdown code fences if present
        $text = preg_replace('/```json?\s*|\s*```/', '', trim($text));
        return json_decode($text, true);
    }

    // ─────────────────────────────────────────────────────────────────
    /**
     * Call Groq API with automatic model fallback.
     * Tries each model in AI_GROQ_MODELS order.
     * Falls back to next model on: 429 (quota), 503, or parsing error.
     */
    private static function callGroq(string $prompt): ?array
    {
        $models = defined('AI_GROQ_MODELS') ? AI_GROQ_MODELS : ['llama-3.3-70b-versatile', 'llama-3.1-8b-instant', 'gemma2-9b-it'];
        $url    = 'https://api.groq.com/openai/v1/chat/completions';

        foreach ($models as $model) {
            $body = json_encode([
                'model'       => $model,
                'messages'    => [
                    ['role' => 'system', 'content' => 'You are a medical AI. Always respond with valid JSON only. No extra text.'],
                    ['role' => 'user',   'content' => $prompt],
                ],
                'temperature' => 0.1,
                'max_tokens'  => 512,
            ]);

            try {
                $ch = curl_init($url);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST           => true,
                    CURLOPT_POSTFIELDS     => $body,
                    CURLOPT_HTTPHEADER     => [
                        'Content-Type: application/json',
                        'Authorization: Bearer ' . AI_API_KEY,
                    ],
                    CURLOPT_TIMEOUT        => AI_TIMEOUT,
                    CURLOPT_SSL_VERIFYPEER => false,
                ]);
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $err      = curl_error($ch);
                curl_close($ch);

                if ($err) {
                    error_log("[TriageAI::callGroq] cURL error ($model): $err");
                    continue; // try next model
                }

                // Rate limit or service unavailable — try next model
                if (in_array($httpCode, [429, 503, 529])) {
                    error_log("[TriageAI::callGroq] HTTP $httpCode on $model — switching to next model");
                    continue;
                }

                $data = json_decode($response, true);
                $text = $data['choices'][0]['message']['content'] ?? null;

                if (!$text) {
                    error_log("[TriageAI::callGroq] Empty response from $model");
                    continue;
                }

                // Strip markdown code fences
                $text   = preg_replace('/```json?\s*|\s*```/', '', trim($text));
                $parsed = json_decode($text, true);

                if ($parsed) {
                    error_log("[TriageAI::callGroq] Success with model: $model");
                    return $parsed;
                }

                error_log("[TriageAI::callGroq] JSON parse failed on $model — trying next");
            } catch (\Throwable $e) {
                error_log("[TriageAI::callGroq] Exception ($model): " . $e->getMessage());
            }
        }

        error_log('[TriageAI::callGroq] All Groq models exhausted.');
        return null;
    }

    // ─────────────────────────────────────────────────────────────────
    private static function callOpenAI(string $prompt): ?array
    {
        $url  = 'https://api.openai.com/v1/chat/completions';
        $body = json_encode([
            'model'    => AI_MODEL,
            'messages' => [
                ['role' => 'system', 'content' => 'You are a medical triage AI. Always respond with valid JSON only.'],
                ['role' => 'user',   'content' => $prompt],
            ],
            'temperature'     => 0.1,
            'max_tokens'      => 512,
            'response_format' => ['type' => 'json_object'],
        ]);

        $response = self::httpPost($url, $body, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . AI_API_KEY,
        ]);
        $data = json_decode($response, true);
        $text = $data['choices'][0]['message']['content'] ?? null;
        return $text ? json_decode($text, true) : null;
    }

    // ─────────────────────────────────────────────────────────────────
    private static function httpPost(string $url, string $body, array $headers): string
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => AI_TIMEOUT,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $response = curl_exec($ch);
        $err      = curl_error($ch);
        curl_close($ch);

        if ($err) throw new \RuntimeException("cURL error: $err");
        return $response;
    }

    // ─────────────────────────────────────────────────────────────────
    /**
     * Rule-based fallback — no API call.
     */
    private static function localFallback(
        array $symptoms, int $pain, string $duration, array $conditions
    ): array {
        $score = 0;

        // High-risk keywords
        $criticalKeywords = ['ألم شديد في الصدر', 'صعوبة في التنفس', 'فقدان الوعي', 'نزيف غير متوقف', 'شلل مفاجئ'];
        foreach ($symptoms as $s) {
            if (in_array($s, $criticalKeywords)) $score += 3;
        }

        // Medium keywords
        $urgentKeywords = ['ارتفاع حرارة شديد', 'دوخة وغثيان', 'ألم شديد في البطن', 'ألم في الظهر والعمود'];
        foreach ($symptoms as $s) {
            if (in_array($s, $urgentKeywords)) $score += 2;
        }

        // Chronic conditions
        $highRiskConditions = ['أمراض قلب', 'سكري', 'ضغط الدم'];
        foreach ($conditions as $c) {
            if (in_array($c, $highRiskConditions)) $score += 1;
        }

        // Pain level
        if ($pain >= 9) $score += 3;
        elseif ($pain >= 7) $score += 2;
        elseif ($pain >= 5) $score += 1;

        // Duration
        if (str_contains($duration, '24')) $score += 2;
        elseif (str_contains($duration, '1–3')) $score += 1;

        // Determine priority & specialty
        if ($score >= 6) {
            $priority  = 'Critical';
            $specialty = 'طب طوارئ';
            $wait      = 'فوري — توجه للطوارئ الآن';
            $reason    = 'أعراض حرجة تستدعي تدخلاً طبياً فورياً.';
        } elseif ($score >= 3) {
            $priority  = 'Medium';
            $specialty = self::guessSpecialty($symptoms);
            $wait      = 'خلال 1-14 يوم';
            $reason    = 'حالة عاجلة تحتاج متابعة سريعة.';
        } else {
            $priority  = 'Routine';
            $specialty = self::guessSpecialty($symptoms);
            $wait      = 'خلال شهر أو أكثر';
            $reason    = 'حالة مستقرة تحتاج متابعة روتينية.';
        }

        return [
            'priority'   => $priority,
            'specialty'  => $specialty,
            'wait_time'  => $wait,
            'reasoning'  => $reason,
            'confidence' => round(min($score / 10, 0.85), 2),
            'source'     => 'local_fallback',
        ];
    }

    private static function guessSpecialty(array $symptoms): string
    {
        $map = [
            'أعصاب'       => ['رأس', 'صداع', 'دوخ', 'دوار', 'عصب', 'شلل', 'خدر', 'تنميل', 'صرع', 'ذاكرة'],
            'طب باطني'    => ['قلب', 'صدر', 'رئة', 'تنفس', 'ضغط', 'سكري', 'كلى', 'كبد', 'حمى', 'حرارة', 'إعياء'],
            'جراحة'        => ['بطن', 'التهاب زائدة', 'فتق', 'كيس', 'ورم', 'عملية'],
            'عظام'         => ['عظم', 'عمود', 'مفصل', 'كسر', 'ظهر', 'ركبة', 'كاحل', 'ورك', 'يد', 'قدم'],
            'أطفال'        => ['طفل', 'رضيع', 'ولد', 'بنت', 'أطفال'],
            'نساء وولادة' => ['نساء', 'حمل', 'ولادة', 'دورة', 'رحم', 'مبيض'],
            'طب طوارئ'    => ['حادث', 'صدمة', 'نزيف', 'إغماء', 'تسمم', 'حريق', 'كسر مفتوح'],
            'طب عام'      => ['زكام', 'إنفلونزا', 'سعال', 'التهاب حلق', 'إرهاق', 'نوم', 'شهية'],
        ];

        $text   = implode(' ', $symptoms);
        $scores = [];

        foreach ($map as $spec => $keywords) {
            $score = 0;
            foreach ($keywords as $kw) {
                if (str_contains($text, $kw)) $score++;
            }
            if ($score > 0) $scores[$spec] = $score;
        }

        if (!empty($scores)) {
            arsort($scores);
            return array_key_first($scores);
        }

        return 'طب عام';
    }

    private static function sanitize(array $r): array
    {
        $allowed = ['Critical', 'Medium', 'Routine'];
        if (!in_array($r['priority'] ?? '', $allowed)) $r['priority'] = 'Routine';
        $r['confidence'] = max(0.0, min(1.0, (float)($r['confidence'] ?? 0.5)));
        return $r;
    }

    // ═════════════════════════════════════════════════════════════════
    // AI SCHEDULING — جدولة ذكية بسياق كامل
    // ═════════════════════════════════════════════════════════════════

    /**
     * Ask the AI to schedule a new patient appointment.
     *
     * Sends the full context (patient history + doctor schedules + existing appointments)
     * to the AI and gets back:
     *   - doctor_id, date, time
     *   - ai_summary   (للطبيب فقط، لا يراه المريض)
     *   - ai_reasoning  (سجل داخلي)
     *   - reasoning (Arabic — للمريض)
     *   - reschedule[] — list of {appointment_id, new_date, new_time}
     *
     * @param string $priority       'Critical' | 'Medium' | 'Routine'
     * @param array  $doctors        Array of doctors with schedule + appointments
     * @param array  $patientContext Patient data: symptoms, pain_level, duration, conditions,
     *                               notes, chronic_diseases, medical_history
     * @return array|null  null on failure (caller should fallback)
     */
    public static function scheduleWithAI(string $priority, array $doctors, array $patientContext = []): ?array
    {
        $prompt = self::buildSchedulePrompt($priority, $doctors, $patientContext);

        try {
            $result = match (AI_PROVIDER) {
                'gemini' => self::callGemini($prompt),
                'groq'   => self::callGroq($prompt),
                'openai' => self::callOpenAI($prompt),
                default  => null,
            };

            if ($result && isset($result['doctor_id'], $result['date'], $result['time'])) {
                return $result;
            }
        } catch (\Throwable $e) {
            error_log('[TriageAI::scheduleWithAI] API failed: ' . $e->getMessage());
        }

        return null; // caller handles fallback
    }

    // ─────────────────────────────────────────────────────────────────
    private static function buildSchedulePrompt(string $priority, array $doctors, array $ctx = []): string
    {
        $today     = date('Y-m-d');
        $dayNames  = ['الأحد','الاثنين','الثلاثاء','الأربعاء','الخميس','الجمعة','السبت'];
        $priorityAr = match($priority) {
            'Critical' => 'حرجة (فورية)',
            'Medium'   => 'عاجلة',
            default    => 'مستقرة (روتينية)',
        };

        // ── بيانات المريض ──
        $sympList   = !empty($ctx['symptoms'])   ? implode('، ', $ctx['symptoms']) : 'غير محدد';
        $painLevel  = $ctx['pain_level'] ?? 'غير محدد';
        $duration   = $ctx['duration']   ?? 'غير محدد';
        $notes      = $ctx['notes']      ?? '';
        $condList   = !empty($ctx['conditions']) ? implode('، ', $ctx['conditions']) : 'لا يوجد';

        $chronicText = 'لا يوجد';
        if (!empty($ctx['chronic_diseases'])) {
            $items = [];
            foreach ($ctx['chronic_diseases'] as $cd) {
                $items[] = is_array($cd) ? ($cd['disease_name'] ?? '') : $cd;
            }
            $chronicText = implode('، ', array_filter($items)) ?: 'لا يوجد';
        }

        $historyText = 'لا يوجد سجلات سابقة';
        if (!empty($ctx['medical_history'])) {
            $historyText = '';
            foreach ($ctx['medical_history'] as $h) {
                $diag  = $h['diagnosis']    ?? 'غير محدد';
                $dnote = $h['doctor_notes'] ?? '';
                $historyText .= "  - تاريخ: {$h['appointment_date']} | تشخيص: {$diag} | ملاحظات: {$dnote}\n";
            }
        }

        $patientSection = <<<PATIENT
بيانات المريض:
الأعراض: {$sympList}
شدة الألم: {$painLevel}/10
مدة الأعراض: {$duration}
الحالات المزمنة: {$condList}
الأمراض المزمنة المسجلة: {$chronicText}
ملاحظات المريض: {$notes}

السجلات الطبية السابقة:
{$historyText}
PATIENT;

        // ── بيانات الأطباء ──
        $doctorsSection = '';
        foreach ($doctors as $doc) {
            $scheduleText = '';
            foreach ($doc['schedule'] as $s) {
                $shiftLabel   = ($s['shift_number'] ?? 1) == 1 ? 'صباحي' : 'مسائي';
                $slotMin      = $s['slot_duration_min'] ?? 30;
                $scheduleText .= "    - {$dayNames[$s['day_of_week']]} ({$shiftLabel}): {$s['start_time']} — {$s['end_time']} | مدة الجلسة: {$slotMin} دقيقة\n";
            }

            $apptText = '';
            foreach ($doc['appointments'] as $a) {
                $typeLabel = ($a['booking_type'] ?? 'regular') === 'smart' ? 'ذكي' : 'عادي';
                $apptText .= "    - ID={$a['id']} | {$a['appointment_date']} {$a['appointment_time']} | أولوية: {$a['priority']} | نوع: {$typeLabel} | المريض: {$a['patient_name']}\n";
            }
            if (!$apptText) $apptText = "    (لا توجد مواعيد قادمة)\n";

            $doctorsSection .= "طبيب ID={$doc['doctor_id']}: {$doc['name']}\n";
            $doctorsSection .= "  جدول الدوام:\n{$scheduleText}";
            $doctorsSection .= "  المواعيد الحالية (3 أيام القادمة):\n{$apptText}\n";
        }

        return <<<PROMPT
أنت نظام جدولة طبية ذكي. مهمتك تحليل حالة المريض وتحديد أفضل موعد.

تاريخ اليوم: {$today}
أولوية المريض الجديد: {$priorityAr}

{$patientSection}

بيانات الأطباء المتاحين:
{$doctorsSection}

قواعد الجدولة — منطق التسلسل (Cascade):

**المبدأ الأساسي:** أعِد جدولة الحد الأدنى من الحجوزات اللازمة فقط. لا تلمس أي يوم لا تأثير فيه.

**الخطوات:**

1. ابنِ خريطة slots كاملة لكل يوم (اليوم وما بعده) من جدول الطبيب (slot_duration_min).

2. ابحث عن أفضل يوم وموعد لـ **المريض الجديد** (حسب أولويته Critical/Medium/Routine).
   - إذا وجد slot فارغ مناسب → لا حاجة لإزاحة أحد.
   - إذا الـ slot المناسب مشغول بمريض أولويته **أقل** → أزِح ذلك المريض (المُزاح الأول).

3. للمريض المُزاح (الأول):
   - ابحث عن أفضل slot في **نفس اليوم** أولاً (في أي وقت فارغ، مع مراعاة أولويته بين حجوزات ذلك اليوم).
   - إن امتلأ اليوم → انتقل لليوم التالي الذي يعمل فيه الطبيب وكرر.
   - إذا هذا الـ slot بدوره مشغول بمريض أولويته أقل → أزِح ذلك المريض (المُزاح الثاني).

4. كرر الخطوة 3 للمُزاح الثاني وهكذا (cascade) حتى لا يبقى مريض بدون موعد.

5. الأيام التي لا علاقة لها بهذا التسلسل لا تُمس إطلاقاً.

**قيود:**
- لا تُزح مريضاً أولويته مساوية أو أعلى من المريض الجديد (Critical/Medium لا تُزاح إلا بأعلى منها).
- لا تُحجز في وقت ماضٍ (اليوم: {$today}).
- تأكد أن كل وقت مُقترح ضمن ساعات دوام الطبيب.
- يجوز إزاحة Routine سواء كان الحجز عادياً أو ذكياً.

أجب بـ JSON **فقط** بدون أي نص إضافي:
{
  "doctor_id": <رقم>,
  "date": "<YYYY-MM-DD>",
  "time": "<HH:MM>",
  "ai_summary": "<ملخص طبي قصير للطبيب — جملتين كحد أقصى — لا يراه المريض>",
  "ai_reasoning": "<تفصيل سبب اختيار هذا الموعد والطبيب — 3 جمل>",
  "reasoning": "<سبب الاختيار بالعربية — جملة واحدة للمريض>",
  "reschedule": [
    { "appointment_id": <رقم>, "new_date": "<YYYY-MM-DD>", "new_time": "<HH:MM>" }
  ]
}

إذا لم تحتج لإعادة جدولة أي موعد، أرجع "reschedule": []
PROMPT;
    }
}
