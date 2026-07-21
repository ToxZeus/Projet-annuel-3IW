<?php if (!empty($success)) : ?>
    <div class="notice notice-success">
        <span class="notice-icon" aria-hidden="true">✓</span>
        <span><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></span>
    </div>
<?php endif; ?>

<?php if (!empty($error)) : ?>
    <div class="notice notice-error">
        <span class="notice-icon" aria-hidden="true">✕</span>
        <span><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></span>
    </div>
<?php endif; ?>