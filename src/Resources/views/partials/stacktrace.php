<?php
/**
 * @var \League\Plates\Template\Template $this
 * @var \SlashTrace\Event $event
 * @var \SlashTrace\Template\TemplateHelper $templateHelper
 */
$i = 0;
$applicationPath = $event->getContext()->getApplicationPath();
?>
<ul class="stacktrace">
    <?php foreach ($event->getExceptions() as $exception): ?>
        <?php $j = 0; ?>
        <li>
            <div>
                <?php if ($i > 0): ?>
                    <span>Previous exception:</span>
                <?php else: ?>
                    <span class="event-level event-level-<?= $event->getLevel(); ?> hidden-xs">
                        <?= $event->getLevel(); ?>
                    </span>
                <?php endif; ?>

                <h2><?= $this->escape($exception->getType()); ?></h2>
                <h3><?= $this->escape($exception->getMessage()); ?></h3>
            </div>

            <ul>
                <?php foreach ($exception->getStackTrace() as $frame): ?>
                    <li data-frame>
                        <span class="icon icon-right"></span>
                        <span class="icon icon-down"></span>

                        <em class="code"><?= $templateHelper->formatStackFrameCall($frame); ?></em>
                        <div class="location">
                            <?php if ($frame->getFile()): ?>
                                <?= $frame->getRelativeFile($applicationPath); ?>:<span><?= $frame->getLine(); ?></span>
                            <?php else: ?>
                                [internal function]
                            <?php endif; ?>
                        </div>

                        <div class="frame-context">
                            <div class="code"><?= $templateHelper->formatStackFrameContext($frame); ?></div>
                            <?php $this->insert("partials/frame/arguments", ["frame" => $frame]); ?>
                        </div>
                    </li>
                    <?php $j++; ?>
                <?php endforeach; ?>
            </ul>
        </li>
        <?php $i++; ?>
    <?php endforeach; ?>
</ul>
