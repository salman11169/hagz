<?php
// ═══════════════════════════════════════════
// AI Configuration — Hagz Clinic Triage Engine
// ═══════════════════════════════════════════

// ── Provider: 'groq' | 'gemini' | 'openai' ─
define('AI_PROVIDER', 'groq');

// ── Groq API Key ─────────────────────────────
// ضع المفتاح الخاص بك هنا
define('AI_API_KEY', getenv('GROQ_API_KEY') ?: 'YOUR_API_KEY_HERE');

// ── Groq Models (in priority order — auto-fallback on quota exhaustion) ──
define('AI_GROQ_MODELS', [
    'llama-3.3-70b-versatile',   // الأقوى — يُستخدم أولاً
    'llama-3.1-8b-instant',      // أسرع — fallback عند انتهاء الكوتا
    'gemma2-9b-it',              // fallback أخير
]);

// ── Default model (first in list) ───────────
define('AI_MODEL', 'llama-3.3-70b-versatile');

// ── Request timeout (seconds) ───────────────
define('AI_TIMEOUT', 20);
