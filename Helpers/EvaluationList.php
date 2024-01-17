<?php

namespace OpinionManagement\Helpers;

use MapasCulturais\App;
use MapasCulturais\Entities\Registration;
use MapasCulturais\Entities\RegistrationEvaluation;

class EvaluationList implements \JsonSerializable
{

    /**
     * @var \MapasCulturais\Entities\Registration $registration
     */
    private $registration;
    /**
     * @var \MapasCulturais\Entities\RegistrationEvaluation[] $registrationEvaluations
     */
    public $registrationEvaluations;
    private $app;

    public function __construct(Registration $registration)
    {
        $this->registration = $registration;
        $this->app = App::i();
        $this->registrationEvaluations = $this->app->repo('RegistrationEvaluation')->findBy(['registration' => $this->registration->id]);
    }


    public function jsonSerialize(): array
    {
        $app = App::i();
        return array_map(function (int $index, RegistrationEvaluation $evaluation): array {
            $evaluationSerialized = $evaluation->jsonSerialize();
            if(!$this->registration->canUser('viewUserEvaluation')) {
                $evaluationSerialized['agent'] = [
                    'id' => $index,
                    'name' => $index+1,
                ];
                $evaluationSerialized['singleUrl'] = $evaluationSerialized['registration']->singleUrl;
            }
            return $evaluationSerialized;
        }, array_keys($this->registrationEvaluations), array_values($this->registrationEvaluations));
    }
}