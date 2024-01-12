<?php

namespace OpinionManagement\Helpers;

use MapasCulturais\App;
use MapasCulturais\Entities\Registration;

class RegistrationEvaluationList implements \JsonSerializable
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
        $index = 1;
        return array_map(function ($evaluation) use (&$index, $app): array {
            $evaluation = $evaluation->jsonSerialize();
            if(!$this->registration->canUser('viewUserEvaluation')) {
                $evaluation['agent'] = [
                    'id' => $index,
                    'name' => $index,
                ];
            }
            $index++;
            return $evaluation;
        }, $this->registrationEvaluations);
    }


}