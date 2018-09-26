<?php
/**
 * @var \League\Plates\Template\Template $this
 * @var \SlashTrace\StackTrace\StackFrame $frame
 * @var \SlashTrace\Template\TemplateHelper $templateHelper
 */
?>
<div class="frame-arguments">
    <h2>Arguments</h2>
    <?php if (count($frame->getArguments())): ?>
        <table>
            <tbody>
                <?php foreach ($frame->getArguments() as $key => $argument): ?>
                    <tr>
                        <td class="code">
                            <?= is_numeric($key) ? "#" : ""; ?><?= $this->escape($key); ?>
                        </td>
                        <td><?= $templateHelper->dump($argument); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No arguments.</p>
    <?php endif; ?>
</div>
