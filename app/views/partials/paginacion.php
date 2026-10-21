<?php /** @var array $r  @var string $base  @var array $qs */
if ($r['pages'] > 1): ?>
<nav class="mt-4"><ul class="pagination justify-content-center">
  <?php for ($i = 1; $i <= $r['pages']; $i++): ?>
    <li class="page-item <?= $i === $r['page'] ? 'active' : '' ?>"><a class="page-link" href="<?= url($base . '?' . http_build_query(array_filter($qs + ['page' => $i]))) ?>"><?= $i ?></a></li>
  <?php endfor; ?></ul></nav>
<?php endif; ?>
