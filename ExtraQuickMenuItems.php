<?php 

/**
 * Some extra quick-menu items to ease everyday usage
 *
 * @since 2016-04-22
 * @author Olle Härstedt
 */
class ExtraQuickMenuItems extends \ls\pluginmanager\PluginBase
{
    static protected $description = 'Extra buttons in the quick-menu';
    static protected $name = 'ExtraQuickMenuItems';

    protected $storage = 'DbStorage';
    protected $settings = array(
        'ExtraQuickMenuItems_info' => array(
            'type' => 'info',
            'content' => '<div class="well col-sm-8"><span class="fa fa-info-circle"></span>&nbsp;&nbsp;Choose which buttons to show in the quick-menu. The buttons are visible to all back-end users. Some buttons will be hidden due to permissions.</div>'
        ),
        'activateDeactivate' => array(
            'type' => 'checkbox',
            'label' => 'Activate/deactivate survey&nbsp;<span class="glyphicon glyphicon-play"></span>',
            'default' => '0',
        ),
        'testSurvey' => array(
            'type' => 'checkbox',
            'label' => 'Test or execute survey&nbsp;<span class="glyphicon glyphicon-cog"></span>',
            'default' => '1',
        ),
        'surveySettings' => array(
            'type' => 'checkbox',
            'label' => 'Survey settings&nbsp;<span class="glyphicon icon-edit"></span>',
            'default' => '1',
        ),
        'tokenManagement' => array(
            'type' => 'checkbox',
            'label' => 'Token management&nbsp;<span class="glyphicon glyphicon-user"></span>',
            'default' => '1',
        ),
        'ExtraQuickMenuItems_responses' => array(
            'type' => 'checkbox',
            'label' => 'Responses&nbsp;<span class="glyphicon icon-browse"></span>',
            'default' => '1',
        ),
        'ExtraQuickMenuItems_statistics' => array(
            'type' => 'checkbox',
            'label' => 'Statistics&nbsp;<span class="glyphicon glyphicon-stats"></span>',
            'default' => '1',
        )
    );

    public function init()
    {
        $this->subscribe('afterQuickMenuLoad');
    }

    public function afterQuickMenuLoad()
    {
        $event = $this->getEvent();
        $settings = $this->getPluginSettings(true);
        
        $buttons = array();
        $data = $event->get('aData');
        $surveyId = $data['surveyid'];
        $activated = $data['activated'];
        $survey = $data['oSurvey'];
        $baselang = $survey->language;

        // Activate/de-activate survey
        if (!$activated) {
            $buttons[] = array(
                'openInNewTab' => false,
                'href' => Yii::app()->getController()->createUrl("admin/survey/sa/activate/surveyid/$surveyId"),
                'tooltip' => gT('Activate survey'),
                'iconClass' => 'glyphicon glyphicon-play navbar-brand'
            );
        }
        else {
            $buttons[] = array(
                'openInNewTab' => false,
                'href' => Yii::app()->getController()->createUrl("admin/survey/sa/deactivate/surveyid/$surveyId"),
                'tooltip' => gT('Stop this survey'),
                'iconClass' => 'glyphicon glyphicon-stop navbar-brand'
            );
        }

        // Test/execute survey
        $buttons[] = array(
            'openInNewTab' => true,
            'href' => Yii::app()->getController()->createUrl("survey/index/sid/$surveyId/newtest/Y/lang/$baselang"),
            'tooltip' => $activated ? gT('Execute survey') : gT('Test survey'),
            'iconClass' => 'glyphicon glyphicon-cog navbar-brand'
        );

        // Survey settings
        $buttons[] = array(
            'openInNewTab' => false,
            'href' => Yii::app()->getController()->createUrl("admin/survey/sa/editlocalsettings/surveyid/$surveyId"),
            'tooltip' => gT('General settings & texts'),
            'iconClass' => 'glyphicon icon-edit navbar-brand'
        );

        // Token management
        $buttons[] = array(
            'openInNewTab' => false,
            'href' => Yii::app()->getController()->createUrl("admin/tokens/sa/index/surveyid/$surveyId"),
            'tooltip' => gT('Token management'),
            'iconClass' => 'glyphicon glyphicon-user navbar-brand'
        );

        // Responses and statistics
        if ($activated) {
            $buttons[] = array(
              'openInNewTab' => false,
              'href' => Yii::app()->getController()->createUrl("admin/responses/sa/index/surveyid/$surveyId/"),
              'tooltip' => gT('Responses'),
              'iconClass' => 'glyphicon icon-browse navbar-brand'
            );

            // Statistics
            $buttons[] = array(
                'openInNewTab' => false,
                'href' => Yii::app()->getController()->createUrl("admin/statistics/sa/index/surveyid/$surveyId"),
                'tooltip' => gT('Statistics'),
                'iconClass' => 'glyphicon glyphicon-stats navbar-brand'
            );
        }

        // Central participant database
        /*
        $buttons[] = array(
            'openInNewTab' => false,
            'href' => Yii::app()->getController()->createUrl("admin/participants/sa/displayParticipants"),
            'tooltip' => gT('Central participant database'),
            'iconClass' => 'glyphicon TODO: Icon navbar-brand'
        );
         */

        $event->set('quickMenuItems', $buttons);
    }
}

