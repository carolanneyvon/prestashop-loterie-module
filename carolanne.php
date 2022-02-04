<?php

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

if (!defined('_PS_VERSION_'))
    exit;

class Carolanne extends Module implements WidgetInterface
{

    public $title;
    public $texte;
    public $dateDebut;
    public $dateFin;
    public $reduction;


    public function __construct()
    {
        $this->name = 'carolanne';
        $this->author = 'carolanne';
        $this->version = '1.0';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = 'Jeu de Noël';
        $this->description = 'Augmentez vos revenus avec un jeu concours';
        $this->ps_versions_compliancy = array('min' => '1.7.0', 'max' => _PS_VERSION_);

        $this->title = Configuration::get('HC_TITLE');
        $this->texte = Configuration::get('HC_TEXTE');
        $this->dateDebut = Configuration::get('HC_DATE_DEBUT');
        $this->dateFin = Configuration::get('HC_DATE_FIN');
        $this->reduction = Configuration::get('HC_REDUCTION');
    }

    /**
     * @return bool true si l'installation s'est bien passée, falsee sinon
     */
    public function install()
    {
        return (parent::install()
            && $this->registerHook('displayHome')
            && $this->registerHook('displayLeftColumn')
            && $this->registerHook('actionFrontControllerSetMedia')
            && Configuration::updateValue('HC_TITLE', 'Mon titre')
            && Configuration::updateValue('HC_TEXTE', 'Le texte à afficher')
            && Configuration::updateValue('HC_DATE_DEBUT', '2021-12-21 00:01:00')
            && Configuration::updateValue('HC_DATE_FIN', '2022-12-31 00:01:00')
            && Configuration::updateValue('HC_REDUCTION', 'La remise doit ce faire par pas de 5%')
        );
    }

    /**
     * @return bool 
     */
    public function uninstall()
    {
        return (parent::uninstall()
            && $this->unregisterHook('displayHome')
            && $this->unregisterHook('displayLeftColumn')
            && $this->unregisterHook('actionFrontControllerSetMedia'));
    }

    /**
     * Méthode pour mettre du CSS et du javascript
     */
    public function hookactionFrontControllerSetMedia($params)
    {
        $this->context->controller->registerStylesheet(
            'module-hc-module-style',
            'modules/' . $this->name . '/views/assets/css/style.css',
            [
                'media' => 'all',
                'priority' => 200,
            ]
        );

        $this->context->controller->registerJavascript(
            'module-hc-module-js',
            'modules/' . $this->name . '/views/assets/js/lib.js',
            [
                'priority' => 999,
                'attribute' => 'async'
            ]
        );
    }

    /**
     * Fonction pour afficher une page de configuration
     */
    public function getContent()
    {
        $success = '';

        if (Tools::isSubmit('submit' . $this->name)) {
            if (Validate::isDateFormat(Tools::getValue('dateDebut')) && Validate::isDateFormat(Tools::getValue('dateFin'))) {
                Configuration::updateValue('HC_TITLE', Tools::getValue('title'));
                Configuration::updateValue('HC_TEXTE', Tools::getValue('texte'));
                Configuration::updateValue('HC_DATE_DEBUT', Tools::getValue('dateDebut'));
                Configuration::updateValue('HC_DATE_FIN', Tools::getValue('dateFin'));
                Configuration::updateValue('HC_REDUCTION', Tools::getValue('reduction'));
                $success = $this->displayConfirmation('Les paramètres ont été mis à jour');
            } else {
                $success = $this->displayError('La date n\'est pas valide');
            }
        }
        return $success . $this->displayForm();
    }

    /**
     * Affichage du formulaire
     */
    public function displayForm()
    {

        $form = [
            'form' => [
                'legend' => [
                    'title' => 'Configuration du module'
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => 'Nom du jeu',
                        'name' => 'title',
                        'required' => true
                    ],
                    [
                        'type' => 'textarea',
                        'label' => 'Descrition du jeu',
                        'name' => 'texte',
                        'cols' => 40,
                        'rows' => 30,
                        'class' => 'rte',
                        'autoload_rte' => true,
                        'required' => true
                    ],
                    [
                        'type' => 'datetime',
                        'label' => 'date de début',
                        'name' => 'dateDebut',
                        'required' => true
                    ],
                    [
                        'type' => 'datetime',
                        'label' => 'date de fin',
                        'name' => 'dateFin',
                        'required' => true
                    ],
                    [
                        'type' => 'text',
                        'label' => 'Réduction maximale en pourcentage',
                        'name' => 'reduction',
                        'required' => true
                    ],
                ],
                'submit' => [
                    'title' => 'Valider',
                    'class' => 'btn btn-default pull-right'
                ]
            ]
        ];

        $helper = new HelperForm();
        //Table de la base de données - Par défaut=table module
        $helper->table = $this->table;
        //le nom du contrôleur - mettre le nom du module par défaut
        $helper->name_controller = $this->name;
        //jeton de sécurité pour vérifier que l'utilisateur est bien administrateur
        $helper->token = Tools::getAdminTokenLite('Adminmodules');
        //Génère l'URL de soumission de formulaire
        $helper->current_index = AdminController::$currentIndex . '&' . http_build_query(['configure' => $this->name]);
        //Le nom de l'action submit
        $helper->submit_action = 'submit' . $this->name;
        //La langue par défaut
        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');

        $helper->fields_value['title'] = Tools::getValue('title', Configuration::get('HC_TITLE'));
        $helper->fields_value['texte'] = Tools::getValue('texte', Configuration::get('HC_TEXTE'));
        $helper->fields_value['dateDebut'] = Tools::getValue('dateDebut', Configuration::get('HC_DATE_DEBUT'));
        $helper->fields_value['dateFin'] = Tools::getValue('dateFin', Configuration::get('HC_DATE_FIN'));
        $helper->fields_value['reduction'] = Tools::getValue('reduction', Configuration::get('HC_REDUCTION'));

        return $helper->generateForm([$form]);
    }

    public function getWidgetVariables($hookName, array $configuration)
    {
        if ($hookName === 'displayHome' && ($this->context->customer->id !== NULL)) {
            return array(
                'title' => Configuration::get('HC_TITLE'),
                'link' => $this->context->link->getModuleLink($this->name, 'display'),
                'texte' => html_entity_decode(Configuration::get('HC_TEXTE')),
                'reduction' => Configuration::get('HC_REDUCTION'),
            );
        } else {
            return array(
                'texte' => html_entity_decode(Configuration::get('HC_TEXTE')),
                'title' => Configuration::get('HC_TITLE'),
                'link' => $this->context->link->getPageLink('authentication'),
                'reduction' => Configuration::get('HC_REDUCTION'),
            );
        }
    }



    public function renderWidget($hookName, array $configuration)
    {
        $variables = $this->getWidgetVariables($hookName, $configuration);
        $this->smarty->assign($variables);
        if ($hookName === 'displayHome') {
            return $this->fetch('module:carolanne/views/templates/hook/homepage.tpl');
        } else {
            return $this->fetch('module:carolanne/views/templates/hook/left.tpl');
        }
    }
}
