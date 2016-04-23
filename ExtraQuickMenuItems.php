<?php 

/**
 * Local exception
 */
class QuickMenuException extends CException {}

/**
 * Small class for buttons. Basically just an
 * array wrapper with some default values.
 *
 * Implements ArrayAccess so core code can
 * use it as an array.
 *
 * @todo Put this in core?
 */
class QuickMenuButton implements ArrayAccess {
    /**
     * @var string - href in anchor
     */
    public $href;

    /**
     * @var string - String in tooltip. Empty string means no tooltip
     */
    public $tooltip = '';

    /**
     * @var string - Class with glyphicon
     */
    public $iconClass;

    /**
     * @var bool - Whether or not to open link in new tab
     */
    public $openInNewTab = false;

    /**
     * @var bool - Whether or not to show button only when survey is active
     */
    public $showOnlyWhenSurveyIsActivated = false;

    /**
     * @var bool - Whether or not to show button only when survey is non-active
     */
    public $showOnlyWhenSurveyIsDeactivated = false;

    public function __construct($options) {
        $this->href = $options['href'];
        $this->tooltip = $options['tooltip'];
        $this->iconClass = $options['iconClass'];

        if (isset($options['openInNewTab'])) {
            $this->openInNewTab = $options['openInNewTab'];
        }

        if (isset($options['showOnlyWhenSurveyIsActivated']))
        {
            $this->showOnlyWhenSurveyIsActivated = $options['showOnlyWhenSurveyIsActivated'];
        }

        if (isset($options['showOnlyWhenSurveyIsDeactivated']))
        {
            $this->showOnlyWhenSurveyIsDeactivated = $options['showOnlyWhenSurveyIsDeactivated'];
        }
    }

    public function offsetExists($offset)
    {
        throw new QuickMenuException("Can't check if offset exists for QuickMenuButton");
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->$offset;
    }

    public function offsetSet($offset, $value)
    {
        throw new QuickMenuException("Can't set offset for QuickMenuButton");
    }

    public function offsetUnset($offset)
    {
        throw new QuickMenuException("Can't unset offset for QuickMenuButton");
    }
}

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
        ),
        'deactivateSurvey' => array(
            'type' => 'checkbox',
            'label' => 'Deactivate survey&nbsp;<span class="glyphicon glyphicon-stop"></span>',
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
        'responses' => array(
            'type' => 'checkbox',
            'label' => 'Responses&nbsp;<span class="glyphicon icon-browse"></span>',
            'default' => '1',
        ),
        'statistics' => array(
            'type' => 'checkbox',
            'label' => 'Statistics&nbsp;<span class="glyphicon glyphicon-stats"></span>',
            'default' => '1',
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
                'showOnlyWhenSurveyIsDeactivated' => true
            )),
            'deactivateSurvey' => new QuickMenuButton(array(
                'href' => Yii::app()->getController()->createUrl("admin/survey/sa/deactivate/surveyid/$surveyId"),
                'tooltip' => gT('Stop this survey'),
                'iconClass' => 'glyphicon glyphicon-stop navbar-brand',
                'showOnlyWhenSurveyIsActivated' => true
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
                'iconClass' => 'glyphicon icon-edit navbar-brand'
            )),
            'tokenManagement' => new QuickMenuButton(array(
                'href' => Yii::app()->getController()->createUrl("admin/tokens/sa/index/surveyid/$surveyId"),
                'tooltip' => gT('Token management'),
                'iconClass' => 'glyphicon glyphicon-user navbar-brand'
            )),
            'responses' => new QuickMenuButton(array(
              'href' => Yii::app()->getController()->createUrl("admin/responses/sa/browse/surveyid/$surveyId/"),
              'tooltip' => gT('Responses'),
              'iconClass' => 'glyphicon icon-browse navbar-brand',
              'showOnlyWhenSurveyIsActivated' => true
            )),
            'statistics' => new QuickMenuButton(array(
                'href' => Yii::app()->getController()->createUrl("admin/statistics/sa/index/surveyid/$surveyId"),
                'tooltip' => gT('Statistics'),
                'iconClass' => 'glyphicon glyphicon-stats navbar-brand',
              'showOnlyWhenSurveyIsActivated' => true
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
     * Return list of buttons that will be shown for this page load
     *
     * @param bool $activated - True if survey is activated
     * @param array $settings - Plugin settings
     * @return array<QuickMenuButton>
     */
    private function getButtonsToShow($activated, $settings)
    {
        $buttonsToShow = array();

        // Loop through all buttons and check settings and activation
        foreach ($this->buttons as $buttonName => $button)
        {
            if ($settings[$buttonName]['current'] === '1')
            {
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

        $this->initialiseButtons($data);
        $buttonsToShow = $this->getButtonsToShow($activated, $settings);

        $event->set('quickMenuItems', $buttonsToShow);
    }
}

