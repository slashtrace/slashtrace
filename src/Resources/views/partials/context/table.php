<?php
/**
 * @var \League\Plates\Template\Template $this
 * @var array $data
 */
?>
<?php if (count($data)): ?>
    <table>
        <tbody>
            <?php foreach ($data as $key => $value): ?>
                <tr>
                    <td><?= $this->escape($key); ?></td>
                    <td class="code"><?= $this->escape($value); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No data</p>
<?php endif; ?>

