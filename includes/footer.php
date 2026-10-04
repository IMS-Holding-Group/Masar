    <footer class="footer">
        <div class="footer-inner">
            <p class="footer-name">مسار</p>
            <?php
            if (!function_exists('masar_support')) {
                $supPath = __DIR__ . '/../config/support.php';
                if (is_file($supPath)) {
                    require_once $supPath;
                }
            }
            $sup = function_exists('masar_support') ? masar_support() : ['email' => '', 'phone' => '', 'phone_display' => ''];
            ?>
            <?php if (($sup['email'] ?? '') !== '' || ($sup['phone'] ?? '') !== '') : ?>
                <p class="footer-support">الدعم الفني:
                    <?php if (($sup['email'] ?? '') !== '') : ?>
                        <a href="mailto:<?php echo htmlspecialchars($sup['email'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($sup['email'], ENT_QUOTES, 'UTF-8'); ?></a>
                    <?php endif; ?>
                    <?php if (($sup['phone_display'] ?? '') !== '' || ($sup['phone'] ?? '') !== '') : ?>
                        <?php if (($sup['email'] ?? '') !== '') : ?> — <?php endif; ?>
                        <a href="tel:<?php echo preg_replace('/\s+/u', '', (string) ($sup['phone'] ?? '')); ?>"><?php echo htmlspecialchars($sup['phone_display'] !== '' ? $sup['phone_display'] : $sup['phone'], ENT_QUOTES, 'UTF-8'); ?></a>
                    <?php endif; ?>
                </p>
            <?php endif; ?>
            <p class="footer-copy">جميع الحقوق محفوظة © 2026</p>
        </div>
    </footer>
