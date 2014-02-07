<?php
/**
Event based rule
*/
namespace Rule;

class EventRule extends Rule
{
     /**
     * @var string
     */
    protected $event;

    /**
     * @var string
     */
    protected $action;

    public function __construct($rule = array()) {

        if(isset($rule['name'])) {
            $this->setName($rule['name']);
        }

        if(isset($rule['type'])) {
            $this->setType($rule['type']);
        }

        if(isset($rule['for'])) {
            $this->setFor($rule['for']);
        }

        if(isset($rule['event'])) {
            $this->setEvent($rule['event']);
        }

        if(isset($rule['action'])) {
            $this->setAction($rule['action']);
        }

    }

    /**
     * Get events trigger event
     * @return string
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Set events trigger event
     * @param  string $name
     */
    public function setEvent($event)
    {
        $this->event = $event;
    }

    /**
     * Get action triggered on event
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set action
     * @param  string $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }


    /**
     * Params we're going to use to create the rule
     * @param  array $params
     */
    public function validate($params)
    {
        $v = new \Valitron\Validator($params);

        //TODO Is this really three validation rules? Should I spilit into separate extended class?
        //TODO this code is replicated in experiment class
        //TODO this doesn't deal with single slide rules e.g. 2,5,6,
        $v::addRule("isRandom", function($field, $value, array $params) {
            //Check randomised blocks are smaller - larger
            //TODO Check randomised blocks slides exist
            //Check slides aren't in more than one block
            $randomArr = explode(',', $value);
            $allSlides = array();
            foreach ($randomArr as $testcase)
            {
              $slides = explode('-', $testcase);
              $allSlides = array_merge($allSlides, $slides);
              if(count($slides) != 2) {
                return false;
              }
              elseif($slides[0] > $slides[1]) {
                return false;
              }
              elseif($slides[0] == $slides[1]) {
                return false;
              }
            }

            if(count(array_unique($allSlides))<count($allSlides))
            {
             return false;
            }

            return true;
        },'random need to be in a valid format like: 3-9,11-14,16-19');

        //expId already guaranteed by routing and _construct
        $v->rule('required', ['name','type','event','action']);
        $v->rule('isRandom',['for']);
        $v->rule('contains', ['type'], ['ClickEvent', 'KeyPressEvent', 'TimeEvent']); //Captalize to match class name
        $v->rule('contains', ['action'], ['nextSlide', 'error']);

        if($v->validate()) {
            return true;
        } else {
            return $v;
        }
    }

    /**
    * Return response as array
    */
    public function toArray() {
        return array(
            'name' => $this->getName(),
            'type' => $this->getType(),
            'for' => $this->getFor(),
            'event' => $this->getEvent(),
            'action' => $this->getAction(),
        );
    }

    /**
    * Return as Mongo Doc ready for insert or update
    */
    public function toMongo() {
        return $this->toArray();
    }


}
