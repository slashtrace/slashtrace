<?php
/**
 * @var \League\Plates\Template\Template $this
 * @var TemplateHelper $templateHelper
 * @var array $data
 */

use SlashTrace\Template\TemplateHelper;

?>
<?php if (count($data)): ?>
    <table>
        <tbody>
            <?php foreach ($data as $key => $value): ?>
                <tr>
                    <td><?= $this->escape($key); ?></td>
                    <td class="code"><?= is_array($value) ? $templateHelper->dump($value) : $this->escape($value); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No data</p>
<?php endif; ?>

