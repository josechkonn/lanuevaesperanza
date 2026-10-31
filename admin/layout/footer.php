        </main>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>window.LNE_BASE = <?= json_encode(rtrim($CONFIG['base_url'], '/')) ?>;</script>
<script src="<?= e(url('assets/js/admin.js')) ?>?v=3"></script>
<script src="<?= e(url('assets/js/tour.js')) ?>?v=1"></script>
<?php require __DIR__ . '/tour_data.php'; ?>
<?= $scripts_extra ?? '' ?>
</body>
</html>
