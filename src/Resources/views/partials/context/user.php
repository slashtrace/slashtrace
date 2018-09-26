<?php
/**
 * @var \League\Plates\Template\Template $this
 * @var \SlashTrace\Context\User $user
 */
$id = $user->getId();
$email = $user->getEmail();
$name = $user->getName();

$data = [];
if ($id) {
    $data["User ID"] = $id;
}
if ($email) {
    $data["Email"] = $email;
}
if ($name) {
    $data["Name"] = $name;
}
?>
<?php $this->insert("partials/context/table", ["data" => $data]); ?>
