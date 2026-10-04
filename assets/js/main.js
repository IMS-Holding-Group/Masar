document.addEventListener('DOMContentLoaded', function() {
    var menuBtn = document.getElementById('menu-toggle');
    var header = document.getElementById('site-header');
    var nav = document.getElementById('main-nav');
    if (menuBtn && nav && header) {
        menuBtn.addEventListener('click', function() {
            var open = header.classList.toggle('is-nav-open');
            menuBtn.classList.toggle('is-open', open);
            menuBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
            menuBtn.setAttribute('aria-label', open ? 'إغلاق القائمة' : 'فتح القائمة');
        });
        nav.querySelectorAll('a').forEach(function(link) {
            link.addEventListener('click', function() {
                if (window.matchMedia('(max-width: 768px)').matches) {
                    header.classList.remove('is-nav-open');
                    menuBtn.classList.remove('is-open');
                    menuBtn.setAttribute('aria-expanded', 'false');
                    menuBtn.setAttribute('aria-label', 'فتح القائمة');
                }
            });
        });
    }
});

// تبديل تبويبات التسجيل
document.querySelectorAll('.tab-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.tab-btn').forEach(function(b) { b.classList.remove('active'); });
        document.querySelectorAll('.tab-content').forEach(function(c) { c.style.display = 'none'; });
        btn.classList.add('active');
        var t = document.getElementById(btn.dataset.tab);
        if (t) t.style.display = 'block';
    });
});

// إغلاق رسائل Alert تلقائياً
document.querySelectorAll('.alert').forEach(function(a) {
    setTimeout(function() {
        a.style.transition = 'opacity 0.35s';
        a.style.opacity = '0';
        setTimeout(function() { if (a.parentNode) a.parentNode.removeChild(a); }, 350);
    }, 4000);
});

// تأكيد الحذف
document.querySelectorAll('.confirm-delete').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
        if (!confirm('هل أنت متأكد؟ لا يمكن التراجع.')) e.preventDefault();
    });
});

// معاينة صورة الملف الشخصي
var picInput = document.getElementById('profile_picture');
var picPreview = document.getElementById('pic-preview');
if (picInput && picPreview) {
    picInput.addEventListener('change', function() {
        var file = this.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function(e) { picPreview.src = e.target.result; };
            reader.readAsDataURL(file);
        }
    });
}

// التحقق من تطابق كلمات المرور
var regForm = document.getElementById('register-form');
if (regForm) {
    regForm.addEventListener('submit', function(e) {
        var p1 = document.getElementById('password');
        var p2 = document.getElementById('confirm_password');
        if (p1 && p2 && p1.value !== p2.value) {
            e.preventDefault();
            alert('كلمة المرور وتأكيدها غير متطابقين');
        }
    });
}

var regFormCo = document.getElementById('register-form-company');
if (regFormCo) {
    regFormCo.addEventListener('submit', function(e) {
        var c1 = document.getElementById('company_password');
        var c2 = document.getElementById('company_confirm_password');
        if (c1 && c2 && c1.value !== c2.value) {
            e.preventDefault();
            alert('كلمة المرور وتأكيدها غير متطابقين');
        }
    });
}

// ظهور تدريجي للعناصر في الصفحة الرئيسية (يُحترم prefers-reduced-motion عبر CSS)
if ('IntersectionObserver' in window) {
    var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (!reduceMotion) {
        var revealEls = document.querySelectorAll('.reveal-on-scroll');
        if (revealEls.length) {
            var revObs = new IntersectionObserver(function(entries) {
                entries.forEach(function(en) {
                    if (en.isIntersecting) {
                        en.target.classList.add('is-visible');
                        revObs.unobserve(en.target);
                    }
                });
            }, { rootMargin: '0px 0px -40px 0px', threshold: 0.08 });
            revealEls.forEach(function(el) { revObs.observe(el); });
        }
    } else {
        document.querySelectorAll('.reveal-on-scroll').forEach(function(el) {
            el.classList.add('is-visible');
        });
    }
} else {
    document.querySelectorAll('.reveal-on-scroll').forEach(function(el) {
        el.classList.add('is-visible');
    });
}
