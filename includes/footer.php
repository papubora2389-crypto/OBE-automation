    <?php if (isLoggedIn()): ?>
            </main>
        </div>
    </div>
    <?php endif; ?>

    <footer class="bg-light text-center py-4 mt-5">
        <div class="container-fluid">
            <p class="mb-0">&copy; 2024 <?php echo SITE_NAME; ?>. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="<?php echo SITE_URL; ?>/assets/js/script.js"></script>
</body>
</html>
