<?php

namespace OpinionManagement\Controllers;

use Doctrine\Common\Collections\Criteria;
use EvaluationMethodTechnical\Plugin as EvaluationTechnicalPlugin;
use MapasCulturais\Controller,
    MapasCulturais\App;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\EvaluationMethodConfigurationMeta;
use MapasCulturais\Entities\Notification;
use MapasCulturais\Entities\Registration;
use MapasCulturais\Entities\RegistrationEvaluation;
use OpinionManagement\Helpers\EvaluationList;

class OpinionManagement extends Controller
{
    public function GET_index(): void
    {
        $app = (new App)->i();

        if(!$app->user->is('superAdmin')) {
            $this->layout = 'error-404';
            return;
        }

        $config = (object) [
            'autopublish' => true
        ];

        $this->render('index', ['config' => $config]);
    }

    public function GET_opinions(): void
    {
        $app = (new App())->i();
        $this->requireAuthentication();

        /**
         * @var $registration Registration
         */
        $registration = $app->repo(Registration::class)->find($this->data['id']);
        if ($registration->canUser('view')) {
            $evaluationsAvg = self::getEvaluationsAvg($registration);
            (new EvaluationTechnicalPlugin())->applyAffirmativePolicies($evaluationsAvg, $registration);

            $opinions = new EvaluationList($registration);
            $this->json([
                'appliedAffirmativePolicy' => $registration->appliedAffirmativePolicy ?? false,
                'evaluationMethod' => (string) $registration->opportunity->evaluationMethodConfiguration->type,
                'criteria' => self::getCriteriaMeta($registration->opportunity),
                'opinions' => $opinions,
                'registration' => $registration,
            ]);
            return;
        }

        $this->json(['permission-denied'], 403);
    }

    public function POST_publishOpinions(): void
    {
        $app = (new App)->i();
        if($app->user->is('guest')) {
            $app->redirect($app->getBaseUrl());
        }

        $opportunity = $app->repo('Opportunity')->find($this->postData['id']);
        if(!$opportunity->isUserAdmin($app->user)) {
            $this->errorJson(['permission-denied'], 403);
            return;
        }

        try {
            $opportunity->setMetadata('publishedOpinions', true);
            $opportunity->save(true);
        } catch (\Exception $e) {
            $this->errorJson(['error' => new \PDOException('Cannot save this data')], 500);
            return;
        }

        $this->notificateUsers($opportunity->id);

        $this->json(['success' => true]);
    }

    public static function notificateUsers(int $opportunityId, bool $verifyPublishingOpinions = true): bool
    {
        $app = (new App)->i();
        $opportunity = $app->repo('Opportunity')->find($opportunityId);
        if($verifyPublishingOpinions && $opportunity->publishedOpinions === false) {
            return false;
        }

        set_time_limit(500);

        $criteria = new Criteria();
        $criteria->where($criteria->expr()->eq('opportunity', $opportunity));
        $criteria->andWhere($criteria->expr()->gt('status', '0'));

        $registrations = $app->repo('Registration')->matching($criteria);
        $count = count($registrations);
        foreach ($registrations as $i => $registration) {
            $notification = new Notification();
            $notification->user = $registration->owner->user;
            $notification->message = sprintf(
                "Sua inscrição <a style='font-weight:bold;' href='/inscricao/{$registration->id}'>%s</a>" .
                " da oportunidade <a style='font-weight:bold;' href='/oportunidade/{$opportunity->id}'/>%s</a>está com os pareceres publicados.",
                $registration->number,
                $opportunity->name
            );
            $notification->save(true);
            $app->log->debug("Notificação ".($i+1)."/$count enviada para o usuário {$registration->owner->user->id} ({$registration->owner->name})");
        }

        return true;
    }

    public static function getCriteriaMeta(Opportunity $opportunity): array
    {
        $app = (new App)->i();
        $criteria = $app->repo(EvaluationMethodConfigurationMeta::class)->findOneBy([
            'key' => 'criteria',
            'owner' => $opportunity->evaluationMethodConfiguration,
        ]);
        $criteria = json_decode($criteria->value, true);
        $finalCriteria = [];
        array_walk($criteria, function ($criterion) use (&$finalCriteria){
            $finalCriteria[$criterion['id']] = $criterion;
        });

        return $finalCriteria;
    }

    public static function getEvaluationsAvg(Registration $registration): float
    {
        $app = (new App)->i();

        $evaluations = $app->repo(RegistrationEvaluation::class)->findBy(['registration' => $registration]);
        $evaluationsAvg = 0;
        foreach ($evaluations as $evaluation) {
            $evaluationsAvg += (float) $evaluation->result;
        }
        $evaluationsAvg /= count($evaluations); // Necessário utilizar a média das avaliações para aplicar as políticas afirmativas

        return $evaluationsAvg;
    }
}
