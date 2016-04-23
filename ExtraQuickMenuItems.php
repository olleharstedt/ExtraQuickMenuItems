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
        'info' => array(
            'type' => 'info',
            'content' => '<div class="well col-sm-8"><span class="fa fa-info-circle"></span>&nbsp;&nbsp;Choose which buttons to show in the quick-menu. The buttons are visible to all back-end users. Some buttons will be hidden due to permissions.</div>'
        ),
        'activateSurvey' => array(
            'type' => 'checkbox',
            'label' => 'Activate survey&nbsp;<span class="glyphicon glyphicon-play"></span>',
            'default' => '0',
            'help' => 'Needed permission: Survey activation - Update'
        ),
        'deactivateSurvey' => array(
            'type' => 'checkbox',
            'label' => 'Deactivate survey&nbsp;<span class="glyphicon glyphicon-stop"></span>',
            'default' => '0',
            'help' => 'Needed permission: Survey activation - Update'
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
            'help' => 'Needed permission: Survey settings - View'
        ),
        'tokenManagement' => array(
            'type' => 'checkbox',
            'label' => 'Token management&nbsp;<span class="glyphicon glyphicon-user"></span>',
            'default' => '1',
            'help' => 'Needed permission: Token - View'
        ),
        'responses' => array(
            'type' => 'checkbox',
            'label' => 'Responses&nbsp;<span class="glyphicon icon-browse"></span>',
            'default' => '1',
            'help' => 'Needed permission: Responses - View'
        ),
        'statistics' => array(
            'type' => 'checkbox',
            'label' => 'Statistics&nbsp;<span class="glyphicon glyphicon-stats"></span>',
            'default' => '1',
            'help' => 'Needed permission: Statistics - View'
        )
    );

    private $buttons = array();

    public function init()
    {
        $this->subscribe('afterQuickMenuLoad');
    }

    /**
     * @param array $data
     * @return void
     */
    private function initialiseButtons(array $data)
    {

        $surveyId = $data['surveyid'];
        $activated = $data['activated'];
        $survey = $data['oSurvey'];
        $baselang = $survey->language;

        $this->buttons = array(
            'activateSurvey' => new QuickMenuButton(array(
                'href' => Yii::app()->getController()->createUrl("admin/survey/sa/activate/surveyid/$surveyId"),
                'tooltip' => gT('Activate survey'),
                'iconClass' => 'glyphicon glyphicon-play navbar-brand',
                'showOnlyWhenSurveyIsDeactivated' => true,
                'neededPermission' => array('surveyactivation', 'update')
            )),
            'deactivateSurvey' => new QuickMenuButton(array(
                'href' => Yii::app()->getController()->createUrl("admin/survey/sa/deactivate/surveyid/$surveyId"),
                'tooltip' => gT('Stop this survey'),
                'iconClass' => 'glyphicon glyphicon-stop navbar-brand',
                'showOnlyWhenSurveyIsActivated' => true,
                'neededPermission' => array('surveyactivation', 'update')
            )),
            'testSurvey' => new QuickMenuButton(array(
                'openInNewTab' => true,
                'href' => Yii::app()->getController()->createUrl("survey/index/sid/$surveyId/newtest/Y/lang/$baselang"),
                'tooltip' => $activated ? gT('Execute survey') : gT('Test survey'),
                'iconClass' => 'glyphicon glyphicon-cog navbar-brand'
            )),
            'surveySettings' => new QuickMenuButton(array(
                'href' => Yii::app()->getController()->createUrl("admin/survey/sa/editlocalsettings/surveyid/$surveyId"),
                'tooltip' => gT('General settings & texts'),
                'iconClass' => 'glyphicon icon-edit navbar-brand',
                'neededPermission' => array('surveysettings', 'read')
            )),
            'tokenManagement' => new QuickMenuButton(array(
                'href' => Yii::app()->getController()->createUrl("admin/tokens/sa/index/surveyid/$surveyId"),
                'tooltip' => gT('Token management'),
                'iconClass' => 'glyphicon glyphicon-user navbar-brand',
                'neededPermission' => array('tokens', 'read')
            )),
            'responses' => new QuickMenuButton(array(
                'href' => Yii::app()->getController()->createUrl("admin/responses/sa/browse/surveyid/$surveyId/"),
                'tooltip' => gT('Responses'),
                'iconClass' => 'glyphicon icon-browse navbar-brand',
                'showOnlyWhenSurveyIsActivated' => true,
                'neededPermission' => array('responses', 'read')
            )),
            'statistics' => new QuickMenuButton(array(
                'href' => Yii::app()->getController()->createUrl("admin/statistics/sa/index/surveyid/$surveyId"),
                'tooltip' => gT('Statistics'),
                'iconClass' => 'glyphicon glyphicon-stats navbar-brand',
                'showOnlyWhenSurveyIsActivated' => true,
                'neededPermission' => array('statistics', 'read')
            ))
        );

        // Central participant database
        /*
        $buttons[] = array(
            'openInNewTab' => false,
            'href' => Yii::app()->getController()->createUrl("admin/participants/sa/displayParticipants"),
            'tooltip' => gT('Central participant database'),
            'iconClass' => 'glyphicon TODO: Icon navbar-brand'
        );
         */
    }

    /**
     * Check if user has permission to show this button
     *
     * @param int $surveyId
     * @param QuickMenuButton $button
     * @return bool
     */
    private function hasPermission($surveyId, $button)
    {

        // Check for permission to show button
        if ($button['neededPermission'] !== null)
        {
            $hasPermission = Permission::model()->hasSurveyPermission(
                $surveyId, 
                $button['neededPermission'][0],
                $button['neededPermission'][1]
            );

            return $hasPermission;
        }

        return true;
    }

    /**
     * Return list of buttons that will be shown for this page load
     *
     * @param int $surveyId
     * @param bool $activated - True if survey is activated
     * @param array $settings - Plugin settings
     * @return array<QuickMenuButton>
     */
    private function getButtonsToShow($surveyId, $activated, $settings)
    {
        $buttonsToShow = array();

        // Loop through all buttons and check settings and activation
        foreach ($this->buttons as $buttonName => $button)
        {
            if ($settings[$buttonName]['current'] === '1')
            {
                if (!$this->hasPermission($surveyId, $button))
                {
                    continue;
                }

                // Check if survey is active and whether or not to show button
                if ($button['showOnlyWhenSurveyIsActivated'] && $activated)
                {
                    $buttonsToShow[] = $button;
                }
                elseif ($button['showOnlyWhenSurveyIsDeactivated'] && !$activated)
                {
                    $buttonsToShow[] = $button;
                }
                elseif (!$button['showOnlyWhenSurveyIsActivated'] &&
                        !$button['showOnlyWhenSurveyIsDeactivated'])
                {
                    $buttonsToShow[] = $button;
                }
            }
        }

        return $buttonsToShow;
    }

    public function afterQuickMenuLoad()
    {
        $event = $this->getEvent();
        $settings = $this->getPluginSettings(true);

        $data = $event->get('aData');
        $activated = $data['activated'];
        $surveyId = $data['surveyid'];

        $this->initialiseButtons($data);
        $buttonsToShow = $this->getButtonsToShow($surveyId, $activated, $settings);

        $event->set('quickMenuItems', $buttonsToShow);
    }
}

