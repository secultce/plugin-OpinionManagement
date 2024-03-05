<?php
/** @var \MapasCulturais\Entities\Opportunity $opportunity */
?>
<button
    class="btn-primary"
    data-id="<?= $opportunity->id ?>"
    ng-if="<?= $opportunity->publishedOpinions ?>"
><?= \MapasCulturais\i::__('Publicar Pareceres') ?></button>