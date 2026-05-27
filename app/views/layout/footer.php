    </div><!-- end flex wrapper -->

    <footer class="footer">
        <div class="container">
            <?php
                $nombreSitio  = get_setting('nombre_sitio', 'AutoEvaluación');
                $correo       = get_setting('correo');
                $celular      = get_setting('celular');
                $facebook     = get_setting('facebook');
                $tiktok       = get_setting('tiktok');
                $logo         = get_setting('logo');
            ?>

            <div class="footer-inner">
                <!-- Brand -->
                <div class="footer-brand">
                    <?php if ($logo): ?>
                        <img src="/uploads/<?= htmlspecialchars($logo) ?>" alt="Logo" class="footer-logo-img">
                    <?php else: ?>
                        <div class="footer-logo-icon"><i class="fas fa-graduation-cap"></i></div>
                    <?php endif; ?>
                    <div>
                        <div class="footer-brand-name"><?= htmlspecialchars($nombreSitio) ?></div>
                        <div class="footer-tagline">Preparación para exámenes de admisión</div>
                    </div>
                </div>

                <!-- Contact info -->
                <div class="footer-contact">
                    <?php if ($correo): ?>
                        <a href="mailto:<?= htmlspecialchars($correo) ?>" class="footer-contact-item">
                            <i class="fas fa-envelope"></i>
                            <span><?= htmlspecialchars($correo) ?></span>
                        </a>
                    <?php endif; ?>
                    <?php if ($celular): ?>
                        <a href="https://wa.me/<?= htmlspecialchars($celular) ?>" target="_blank" class="footer-contact-item">
                            <i class="fab fa-whatsapp"></i>
                            <span>+<?= htmlspecialchars($celular) ?></span>
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Social links -->
                <div class="footer-social">
                    <?php if ($facebook): ?>
                        <a href="<?= htmlspecialchars($facebook) ?>" target="_blank" class="footer-social-btn" title="Facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                    <?php endif; ?>
                    <?php if ($tiktok): ?>
                        <a href="<?= htmlspecialchars($tiktok) ?>" target="_blank" class="footer-social-btn" title="TikTok">
                            <i class="fab fa-tiktok"></i>
                        </a>
                    <?php endif; ?>
                    <?php if ($celular): ?>
                        <a href="https://wa.me/<?= htmlspecialchars($celular) ?>" target="_blank" class="footer-social-btn footer-social-wa" title="WhatsApp">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> <strong><?= htmlspecialchars($nombreSitio) ?></strong> &mdash; Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <script src="<?= asset('js/main.js') ?>"></script>
    <?php if (!empty($loadExamJs)): ?>
        <script src="<?= asset('js/exam.js') ?>"></script>
    <?php endif; ?>
</body>
</html>
