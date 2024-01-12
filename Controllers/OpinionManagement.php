<?php

namespace OpinionManagement\Controllers;

use http\Url;
use MapasCulturais\Controller,
    MapasCulturais\App;
use MapasCulturais\Entities\Agent;
use OpinionManagement\Helpers\RegistrationEvaluationList;

class OpinionManagement extends Controller
{
    public function GET_index(): void
    {
        $app = App::i();

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
        $app = App::i();
        if($app->user->is('guest')) {
            $app->redirect($app->getBaseUrl());
        }

        $registration = $app->repo('Registration')->find($this->getData['id']);
        $opinions = [];
        if(!$registration->canUser('view')) {
            $opinions = $app->repo('RegistrationEvaluation')->findBy(['registration' => $registration->id]);
            $opinions = new RegistrationEvaluationList($opinions);
            echo $this->json($opinions);
            exit;
        }

//        if(!$registration->canUser('viewUserEvaluation')){
//            dump($opinions[0]->jsonSerialize());
//            dump($opinions[0]);//new \ArrayObject(['name' => '1', 'singleUrl' => $registration->singleUrl]);
//        }
//        $this->json($opinions);
    }
}