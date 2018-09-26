<?php
/**
 * @var \League\Plates\Template\Template $this
 * @var \SlashTrace\Context\Breadcrumbs\Breadcrumb[] $crumbs
 * @var \SlashTrace\Template\TemplateHelper $templateHelper
 */
?>
<ol class="breadcrumbs">
    <?php foreach ($crumbs as $crumb): ?>
        <?php $data = $crumb->getData(); ?>
        <li>
            <time><?= $crumb->getDateTime()->format("H:i:s"); ?></time>
            <strong><?= $this->escape($crumb->getTitle()); ?></strong>
            <?php if ($data): ?>
                <?= $templateHelper->dump($data); ?>
            <?php endif; ?>
        </li>
    <?php endforeach; ?>
</ol>
