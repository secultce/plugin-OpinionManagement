<div class="registration-fieldset">
    <h4>Publicação de Pareceres</h4>
    <p>Deseja que os pareceres desta fase/oportunidade sejam publicados para os proponentes automaticamente ao publicar os resultados?</p>
    <span class="js-editable"
          data-edit="autopublishOpinions"
          data-original-title="Publicar pareceres automaticamente"
    >
        <?= /** @var \MapasCulturais\Entities\Opportunity $opportunity */
        $opportunity->getMetadata('autopublishOpinions') ?>
    </span>
    <br><br>
    <em>Caso marque "Não" aparecerá um botão para publicar pareceres manualmente na aba de inscrições.</em>
</div>
