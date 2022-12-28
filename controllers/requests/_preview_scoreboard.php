<div class="scoreboard-item title-value">
    <h4><?= __('Number') ?></h4>
    <p><?= e($formModel->number) ?></p>
    <p class="description"><?= __('Created') ?>: <?= e($formModel->created_at) ?></p>
</div>

<?php if ($formModel->ip): ?>
    <div class="scoreboard-item title-value">
        <h4><?= __('IP') ?></h4>
        <p><?= e($formModel->ip) ?></p>
        <p class="description"><?= __('Referer') ?>: <?= e($formModel->referer) ?></p>
    </div>
<?php endif ?>
