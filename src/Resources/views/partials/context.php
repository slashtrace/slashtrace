<?php
/**
 * @var \League\Plates\Template\Template $this
 * @var \SlashTrace\Event $event
 * @var \SlashTrace\Template\TemplateHelper $templateHelper
 */
$context = $event->getContext();
$httpRequest = $context->getHTTPRequest();

$getData = $httpRequest->getGetData();
$postData = $httpRequest->getPostData();

$hasCustomData = $context->hasCustomData();

$user = $context->getUser();
$release = $context->getRelease();
$breadcrumbs = $context->getBreadcrumbs();
?>

<ul class="tabs">
    <li data-tab-group="context" data-tab="request" class="active">
        <a href="javascript:void(0);">
            <span class="icon icon-globe"></span>
            <span class="tab-label">Request data</span>
            <span class="tab-label-short">Request</span>
        </a>
    </li>
    <li data-tab-group="context" data-tab="server">
        <a href="javascript:void(0);">
            <span class="icon icon-server"></span>
            <span class="tab-label">Server data</span>
            <span class="tab-label-short">Server</span>
        </a>
    </li>
    <?php if ($hasCustomData): ?>
        <li data-tab-group="context" data-tab="custom">
            <a href="javascript:void(0);">
                <span class="icon icon-user"></span>
                <span class="tab-label">Custom data</span>
                <span class="tab-label-short">Custom</span>
            </a>
        </li>
    <?php endif; ?>
</ul>

<div class="tab-pane active" data-tab-group="context" data-tab="request">
    <div class="box">
        <h3>GET</h3>
        <?php if (empty($getData)): ?>
            <p>No data</p>
        <?php else: ?>
            <?= $templateHelper->dump($getData); ?>
        <?php endif; ?>
    </div>

    <hr>

    <div class="box">
        <h3>POST</h3>
        <?php if (empty($postData)): ?>
            <p>No data</p>
        <?php else: ?>
            <?= $templateHelper->dump($postData); ?>
        <?php endif; ?>
    </div>

    <hr>

    <div class="box">
        <h3>Cookies</h3>
        <?php $this->insert("partials/context/table", [
            "data" => $httpRequest->getCookies()
        ]); ?>
    </div>

    <hr>

    <div class="box">
        <h3>Headers</h3>
        <?php $this->insert("partials/context/table", [
            "data" => $httpRequest->getHeaders()
        ]); ?>
    </div>
</div>

<div class="tab-pane" data-tab-group="context" data-tab="server">
    <?php $this->insert("partials/context/table", [
        "data" => $context->getServer()
    ]); ?>
</div>

<?php if ($hasCustomData): ?>
    <div class="tab-pane" data-tab-group="context" data-tab="custom">
        <?php if ($user): ?>
            <div class="box">
                <h3>Affected user</h3>
                <?php $this->insert("partials/context/user", [
                    "user" => $user
                ]); ?>
            </div>
        <?php endif; ?>

        <?php if ($breadcrumbs): ?>
            <hr>
            <div class="box">
                <h3>Breadcrumbs</h3>
                <?php $this->insert("partials/context/breadcrumbs", ["crumbs" => $breadcrumbs->getCrumbs()]) ?>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>
