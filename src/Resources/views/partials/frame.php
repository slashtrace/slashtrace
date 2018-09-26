<?php
/**
 * @var \League\Plates\Template\Template $this
 * @var \SlashTrace\Event $event
 * @var \SlashTrace\Template\TemplateHelper $templateHelper
 */
$i = 0;
?>
<?php foreach ($event->getExceptions() as $exception): ?>
    <?php $j = 0; ?>
    <?php foreach ($exception->getStackTrace() as $frame): ?>
        <?php $context = $frame->getContext(); ?>
        <div class="frame-context <?= $i == 0 && $j == 0 ? "active" : ""; ?>" data-frame>
            <?php if (!empty($context)): ?>
                <div class="code">
                    <h2><?= $frame->getFile(); ?></h2>
                    <?= $templateHelper->formatStackFrameContext($frame); ?>
                </div>
            <?php endif; ?>

            <?php $this->insert("partials/frame/arguments", [
                "frame" => $frame
            ]); ?>
        </div>

        <?php $j++; ?>
    <?php endforeach; ?>
    <?php $i++; ?>
<?php endforeach; ?>