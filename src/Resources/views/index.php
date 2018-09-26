<?php
/**
 * @var \League\Plates\Template\Template $this
 * @var string $pageTitle
 * @var \SlashTrace\Template\ResourceLoader $resourceLoader
 * @var \SlashTrace\Event $event
 */
$this->layout("layout");
?>
<a href="javascript:void(0);" class="menu visible-xs">
    <div>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
    </div>
</a>
<div class="event">
    <div class="event-inner">
        <?php $this->insert("partials/stacktrace"); ?>
    </div>
</div>
<div class="frame">
    <?php $this->insert("partials/frame"); ?>
</div>
<div class="context">
    <?php $this->insert("partials/context"); ?>
</div>