<?php

namespace app\widgets;

use Yii;
use app\models\Project;
use app\models\Vote as VoteModel;
use yii\base\InvalidConfigException;
use yii\base\Widget;
use yii\bootstrap\Html;
use yii\helpers\Url;

class Like extends Widget
{
    /**
     * @var Project
     */
    public $project;

    /**
     * @throws InvalidConfigException
     */
    public function init()
    {
        if ($this->project === null) {
            throw new InvalidConfigException('Vote widget project property is not set.');
        }
    }

    /**
     * @return string
     */
    public function run()
    {
        $buttonClass = [];
        $buttonClass[VoteModel::VALUE_UP] = ['button', 'text-muted'];

        if (Yii::$app->user->isGuest) {
            $buttonClass[VoteModel::VALUE_UP] = ['disabled'];
        } else {
            $vote = VoteModel::getVote($this->project->id, Yii::$app->user->id);
            if ($vote) {
                $buttonClass[$vote->value][] = 'like';
            }
        }

        $voteUpHtml = Html::tag(
            'span',
            Html::icon('heart'),
            [
                'data-id' => $this->project->id,
                'data-endpoint' => Url::to(['/api1/project/vote', 'uuid' => $this->project->uuid]),
                'data-value' => VoteModel::VALUE_UP,
                'class' => $buttonClass[VoteModel::VALUE_UP],
                'title' => Yii::t('vote', 'Up'),
            ]
        );


        $voteValueHtml = Html::tag(
            'span',
            $this->project->votingResult,
            [
                'class' => 'value',
            ]
        );

        return Html::tag('div', $voteUpHtml . $voteValueHtml, ['class' => 'vote']);
    }
}
