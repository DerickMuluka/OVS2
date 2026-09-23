</main>
<footer class="site-footer">
    <p>© <?= date('Y') ?> VoteFlow — Online Voting System.</p>
</footer>
<script src="<?= $basePath ?? '' ?>assets/js/main.js"></script>
<?php if (!empty($useCharts)): ?>
<script src="<?= $basePath ?? '' ?>assets/js/charts.js"></script>
<?php endif; ?>
</body>
</html>