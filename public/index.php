<?php
session_start();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>شفاء+ - الصفحة الرئيسية</title>

    <!-- Bootstrap RTL -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Boxicons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <!-- Google Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;900&family=Tajawal:wght@300;400;500;700;900&display=swap"
        rel="stylesheet">
    <link href="../assets/css/index.css" rel="stylesheet">

</head>

<body>
    <!-- Loading Animation -->
    <div class="loading-animation" id="loadingAnimation">
        <div class="loader"></div>
    </div>

    <!-- Animated Background -->
    <div class="bg-animated"></div>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="index.php" style="font-weight: 800; font-size: 1.5rem;">
                <i class='bx bx-plus-medical' style="color: #2563eb; margin-left: 5px;"></i>
                شفاء<span style="color: #2563eb;">+</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#home">الرئيسية</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#priority">نظام الأولويات</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#features">المميزات</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">عن النظام</a>
                    </li>
                </ul>
                <div class="d-flex gap-2">
                    <button class="btn btn-login" onclick="window.location.href='../auth/login.php'">
                        <i class="fas fa-sign-in-alt ms-2"></i>
                        تسجيل الدخول
                    </button>
                    <button class="btn btn-register" onclick="handleBooking()">
                        <i class="fas fa-user-plus ms-2"></i>
                        حجز موعد
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section" id="home">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 hero-content">
                    <h1 class="hero-title">
                        نظام الحجز الطبي
                        <span class="highlight">الذكي والمتطور</span>
                    </h1>
                    <p class="hero-subtitle">
                        أول نظام عربي يفرز المواعيد حسب حالتك الصحية بدلاً من الأسبقية الزمنية. نضمن عدم تأخر أي حالة
                        طارئة وتوفير الرعاية الصحية المناسبة في الوقت المناسب.
                    </p>
                    <div class="hero-buttons">
                        <button class="btn btn-primary-hero" onclick="handleBooking()">
                            <i class="fas fa-calendar-check ms-2"></i>
                            احجز موعدك الآن
                        </button>
                        <button class="btn btn-secondary-hero">
                            <i class="fas fa-play-circle ms-2"></i>
                            شاهد كيف يعمل
                        </button>
                    </div>
                </div>
                <div class="col-lg-6 hero-image">
                    <svg viewBox="0 0 500 500" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <linearGradient id="grad1" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" style="stop-color:#0EA5E9;stop-opacity:1" />
                                <stop offset="100%" style="stop-color:#10B981;stop-opacity:1" />
                            </linearGradient>
                        </defs>
                        <circle cx="250" cy="250" r="200" fill="url(#grad1)" opacity="0.1" />
                        <circle cx="250" cy="250" r="150" fill="url(#grad1)" opacity="0.15" />
                        <circle cx="250" cy="250" r="100" fill="url(#grad1)" opacity="0.2" />
                        <path d="M250 150 L250 350 M150 250 L350 250" stroke="url(#grad1)" stroke-width="15"
                            stroke-linecap="round" />
                        <circle cx="250" cy="250" r="30" fill="white" />
                        <circle cx="250" cy="250" r="20" fill="url(#grad1)" />
                    </svg>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="container">
        <div class="stats-section">
            <div class="row">
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-user-injured"></i>
                        </div>
                        <div class="stat-number">50,000+</div>
                        <div class="stat-label">مريض مسجل</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-stethoscope"></i>
                        </div>
                        <div class="stat-number">250+</div>
                        <div class="stat-label">طبيب متخصص</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-hospital"></i>
                        </div>
                        <div class="stat-number">35+</div>
                        <div class="stat-label">مركز طبي</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="stat-number">98%</div>
                        <div class="stat-label">رضا المرضى</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Priority System Section -->
    <section class="priority-section" id="priority">
        <div class="container">
            <h2 class="section-title">نظام الأولويات الذكي</h2>
            <p class="section-subtitle">نظام فريد يصنف حالتك تلقائياً ويوفر لك الرعاية المناسبة</p>

            <div class="row">
                <div class="col-lg-4 col-md-6">
                    <div class="priority-card normal">
                        <div class="priority-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h3 class="priority-title">حالة مستقرة</h3>
                        <p class="priority-description">
                            الحالات الروتينية والفحوصات الدورية التي لا تتطلب تدخلاً عاجلاً
                        </p>
                        <ul class="priority-features list-unstyled">
                            <li><i class="fas fa-calendar"></i> مواعيد منتظمة</li>
                            <li><i class="fas fa-clock"></i> جدولة مرنة</li>
                            <li><i class="fas fa-user-md"></i> اختيار الطبيب المفضل</li>
                        </ul>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="priority-card urgent">
                        <div class="priority-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <h3 class="priority-title">حالة عاجلة</h3>
                        <p class="priority-description">
                            حالات تتطلب اهتماماً سريعاً وتحصل على أولوية في الحجز
                        </p>
                        <ul class="priority-features list-unstyled">
                            <li><i class="fas fa-bolt"></i> أولوية في المواعيد</li>
                            <li><i class="fas fa-bell"></i> تنبيه الطبيب المباشر</li>
                            <li><i class="fas fa-ambulance"></i> متابعة مستمرة</li>
                        </ul>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="priority-card emergency">
                        <div class="priority-icon">
                            <i class="fas fa-hospital"></i>
                        </div>
                        <h3 class="priority-title">حالة حرجة</h3>
                        <p class="priority-description">
                            حالات طوارئ تتطلب تدخلاً فورياً مع توجيه مباشر للمستشفى
                        </p>
                        <ul class="priority-features list-unstyled">
                            <li><i class="fas fa-siren"></i> إنذار فوري للطوارئ</li>
                            <li><i class="fas fa-location-dot"></i> تحديد الموقع GPS</li>
                            <li><i class="fas fa-phone-volume"></i> اتصال مباشر 24/7</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section" id="features">
        <div class="container">
            <h2 class="section-title">مميزات النظام</h2>
            <p class="section-subtitle">تقنيات متقدمة لتجربة طبية متميزة</p>

            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-robot"></i>
                        </div>
                        <h3 class="feature-title">تصنيف ذكي آلي</h3>
                        <p class="feature-description">
                            استبيان طبي متقدم يحلل حالتك بدقة ويصنفها تلقائياً بناءً على خوارزميات طبية معتمدة
                        </p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h3 class="feature-title">أمان البيانات</h3>
                        <p class="feature-description">
                            تشفير متقدم لجميع البيانات الطبية مع الامتثال الكامل لمعايير الخصوصية الطبية
                        </p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <h3 class="feature-title">سهولة الاستخدام</h3>
                        <p class="feature-description">
                            واجهة مستخدم بسيطة وسلسة على جميع الأجهزة - كمبيوتر، تابلت، أو موبايل
                        </p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-bell"></i>
                        </div>
                        <h3 class="feature-title">تنبيهات ذكية</h3>
                        <p class="feature-description">
                            إشعارات فورية عبر الرسائل والبريد الإلكتروني لتذكيرك بمواعيدك ومتابعة حالتك
                        </p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3 class="feature-title">تقارير تفصيلية</h3>
                        <p class="feature-description">
                            سجل طبي كامل ومتابعة مستمرة لحالتك مع تقارير شاملة لك وللأطباء
                        </p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-headset"></i>
                        </div>
                        <h3 class="feature-title">دعم متواصل</h3>
                        <p class="feature-description">
                            فريق دعم فني متاح على مدار الساعة للإجابة على استفساراتك ومساعدتك
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-card">
                <h2 class="cta-title">ابدأ رحلتك الصحية معنا اليوم</h2>
                <p class="cta-description">
                    انضم لآلاف المرضى الذين وثقوا بنا واستمتع بتجربة طبية متطورة
                </p>
                <button class="cta-button">
                    <i class="fas fa-user-plus ms-2"></i>
                    سجل الآن مجاناً
                </button>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h3 class="footer-brand">
                        <i class="fas fa-heartbeat ms-2"></i>
                        نظام الفرز الذكي
                    </h3>
                    <p class="footer-description">
                        منصة طبية متقدمة تعيد تعريف تجربة الحجوزات الطبية من خلال نظام أولويات ذكي يضع صحتك في المقام
                        الأول.
                    </p>
                    <div class="social-links">
                        <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>

                <div class="col-lg-2 col-md-4 mb-4">
                    <h4 class="footer-title">روابط سريعة</h4>
                    <ul class="footer-links">
                        <li><a href="#home">الرئيسية</a></li>
                        <li><a href="#priority">نظام الأولويات</a></li>
                        <li><a href="#features">المميزات</a></li>
                        <li><a href="#about">عن النظام</a></li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-4 mb-4">
                    <h4 class="footer-title">الخدمات</h4>
                    <ul class="footer-links">
                        <li><a href="#">حجز موعد</a></li>
                        <li><a href="#">استشارة طبية</a></li>
                        <li><a href="#">السجل الطبي</a></li>
                        <li><a href="#">خدمات الطوارئ</a></li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-4 mb-4">
                    <h4 class="footer-title">تواصل معنا</h4>
                    <ul class="footer-links">
                        <li><i class="fas fa-phone ms-2"></i> 800-123-4567</li>
                        <li><i class="fas fa-envelope ms-2"></i> info@smarttriage.com</li>
                        <li><i class="fas fa-map-marker-alt ms-2"></i> الرياض، المملكة العربية السعودية</li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; 2024 نظام الفرز الذكي. جميع الحقوق محفوظة.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Loading Animation
        window.addEventListener('load', function () {
            setTimeout(function () {
                document.getElementById('loadingAnimation').classList.add('hidden');
            }, 1000);
        });

        // Smooth Scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Navbar Shadow on Scroll
        window.addEventListener('scroll', function () {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.style.boxShadow = '0 4px 30px rgba(0, 0, 0, 0.1)';
            } else {
                navbar.style.boxShadow = '0 4px 30px rgba(0, 0, 0, 0.05)';
            }
        });

        // Animate on Scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function (entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        document.querySelectorAll('.priority-card, .feature-card, .stat-card').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(30px)';
            el.style.transition = 'all 0.6s ease';
            observer.observe(el);
        });
    </script>

    <!-- Login Required Modal -->
    <div class="modal fade" id="loginRequiredModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius:16px; border:none; text-align:center;">
                <div class="modal-body p-5">
                    <div class="mb-4" style="font-size:3.5rem; color:#f59e0b;">
                        <i class="fas fa-lock"></i>
                    </div>
                    <h3 class="fw-bold mb-3">تسجيل الدخول مطلوب</h3>
                    <p class="text-muted mb-4">يجب عليك تسجيل الدخول بحساب مريض لتتمكن من حجز موعد جديد في النظام.</p>
                    <div class="d-flex gap-2 justify-content-center">
                        <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">إلغاء</button>
                        <a href="../auth/login.php" class="btn btn-primary rounded-pill px-4" style="background:#0d9488; border-color:#0d9488;">تسجيل الدخول</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function handleBooking() {
            <?php if (isset($_SESSION['user_id']) && isset($_SESSION['role_id']) && $_SESSION['role_id'] == 3): ?>
                window.location.href = '../patient/booking-new.php';
            <?php else: ?>
                var loginModal = new bootstrap.Modal(document.getElementById('loginRequiredModal'));
                loginModal.show();
            <?php endif; ?>
        }
    </script>
</body>

</html>