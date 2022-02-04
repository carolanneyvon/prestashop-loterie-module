<?php

class CarolanneDisplayModuleFrontController extends ModuleFrontController
{
    public function init()
    {
        parent::init();
        if ($this->context->customer->id !== NULL) {
            $this->context->smarty->assign(
                array(
                    'title' => $this->module->title,
                    'texte' => $this->module->texte,
                    'dateDebut' => $this->module->dateDebut,
                    'dateFin' => $this->module->dateFin,
                    'reduction' => $this->module->reduction,
                    'promotion' => $this->getPromotion()
                )
            );
        } else {
            $this->context->smarty->assign(
                array(
                    'title' => $this->module->title,
                    'texte' => $this->module->texte,
                    'dateDebut' => $this->module->dateDebut,
                    'dateFin' => $this->module->dateFin,
                    'reduction' => $this->module->reduction,
                    'promotion' => false

                )
            );
        }
    }


    public function initContent()
    {
        parent::initContent();
        $this->context->smarty->assign(['objet' => $this->context->customer]);
        return $this->setTemplate('module:carolanne/views/templates/front/display.tpl');
    }


    /**
     * La fonction créé une règle panier
     * @return array Un tableau qui contient les variables smarty
     */
    public function getPromotion()
    {
        $promotion = new CartRule();
        $promotion->name[1] = $this->module->title;
        $promotion->code = $this->codeAleatoire();
        $promotion->reductionFinale = (rand(0, 10)) * ((int)Configuration::get('HC_REDUCTION')) / 10;
        $promotion->date_from = Configuration::get('HC_DATE_DEBUT');
        $promotion->date_to = Configuration::get('HC_DATE_FIN');
        $promotion->id_customer = $this->context->customer->id;
        $promotion->add();
        return array(
            'name' => $promotion->name[1],
            'code' => $promotion->code,
            'percent' => $promotion->reductionFinale
        );
    }

    /**
     * La fonction génére une chaine aléatoire d'une longueur donnée en paramètre
     * @param $longueur = longueur de la chaine
     * @return string La chaine aléatoire générée
     */
    private function codeAleatoire($longueur = 8)
    {
        $caracteres = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $longueurMax = strlen($caracteres);
        $chaineAleatoire = '';
        for ($i = 0; $i < $longueur; $i++) {
            $chaineAleatoire .= $caracteres[rand(0, $longueurMax - 1)];
        }
        return $chaineAleatoire;
    }
}
