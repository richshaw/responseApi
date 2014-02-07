<?php
namespace Response;

/**
* Timed response experiment
*/
class Experiment
{

     /**
     * @var string
     */
    protected $id;

     /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $body;

    /**
     * @var string
     */
    protected $random;

    /**
     * @var string
     */
    protected $input;

    /**
     * @var bool
     */
    protected $error;

    /**
     * @var string
     */
    protected $collection = 'experiment';

    /**
     * @var timestamp
     */
    protected $created;

    /**
     * @var int
     */
    protected $meta;


    /**
     * @var array
     */
    protected $rules = array();

    /**
     * Get experiment id
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set experiment id
     * @param  string $title
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Get experiment title
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set experiment title
     * @param  string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Get experiment body HTML/SVG
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set experiment Body
     * @param  string $body
     */
    public function setBody($body)
    {
        $this->body = $body;
    }

    /**
     * Get list of slides to randomise
     * @return mixed
     */
    public function getRandom()
    {
        return $this->random;
    }

    /**
     * Set list of slides to randomise
     * @param  mixed $random
     */
    public function setRandom($random)
    {
        $this->random = $random;
    }

    /**
     * Get comma separated list of valid input keys
     * @return mixed
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * Set comma separated list of slides to randomise
     * @param  mixed $random
     */
    public function setInput($input)
    {
        $this->input = $input;
    }

    /**
     * Get if experiment should halt on input error
     * @return bool
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Set if experiment should halt on input error
     * @param  bool $error
     */
    public function setError($error)
    {
        $this->error = (bool) $error;
    }


    /**
     * Get created date as UNIX time
     * @return timetsamp
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set created date as UNIX time
     * @param  mixed $random
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }


    /**
     * Get where to retrive meta data from url, data attribute etc
     * @return meta
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * Set where to retrieve meta data from
     * @param  int $meta
     */
    public function setMeta($meta)
    {
        $this->meta = $meta;
    }


     /**
     * Rules for experiment
     * @return array
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * Set array of rule obejects
     * @param  array $meta
     */
    public function setRules($rules)
    {
        $this->rules = $rules;
    }

    /**
     * Params we're going to use to create the experiment
     * @param  mixed $params
     */
    public function validate($params)
    {
        $v = new \Valitron\Validator($params);

        //TODO Is this really three validation rules? Should I spilit into separate extended class?
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

        $v::addRule('isInput', function($field, $value, array $params) {
              //Check input responses are alphanumeric
              $validArr = explode(',', $value);
              foreach ($validArr as $testcase)
              {
                  if($testcase == "")
                  {
                    return false;
                  }
                  elseif (!ctype_alnum($testcase))
                  {
                    return false;
                  }
              }
            return true;
        }, 'input responses are aren\'t alphanumeric');

        $v::addRule('isBool', function($field, $value, array $params) {
            $value = (bool) $value;
            return is_bool($value);
        }, '{field} must be boolean');

        $v->rule('required', array('title', 'body', 'input', 'error','meta'));
        $v->rule('isRandom',array('random'));
        $v->rule('isInput',array('input'));
        $v->rule('isBool',array('error'));
        $v->rule('numeric',array('meta'));

        if($v->validate()) {
            return true;
        } else {
            return $v;
        }

    }

    /**
    * Save experiment to DB
    * @param  mixed $params
    * @param  Database $db
    */
    public function save(Database $db) {
        //Doc to save to DB
        $doc = $this->toMongo();

        if(!isset($this->id)) {
            //Save new experiment
            $write = $db->insert($this->collection,$doc);
            $this->id = (string) $write['_id'];

            $responses = new Responses($this->id);
            $responses->createCollection($db);
        }
        else {
            //Update existing experiment
            $write = $db->update($this->collection,$this->id,$doc);
        }
        return true;
    }

    /**
    * Load experiment from DB
    * @param  string $id
    * @param  Database $db
    */
    public function load($id, Database $db) {
        $doc = $db->findOne($this->collection,$id);

        $this->setId((string) $doc['_id']);
        $this->setTitle($doc['title']);
        $this->setBody($doc['body']);
        $this->setInput($doc['input']);
        $this->setError($doc['error']);
        $this->setRandom($doc['random']);
        $this->setCreated($doc['created']->sec);
        $this->setMeta($doc['meta']);

        $rules = array();
        foreach ($doc['rules'] as $rule) {
            $ruleClass = '\Rule\\' . $rule['type'] . 'Rule';
            $rules[] = new $ruleClass($rule);
        }

        $this->setRules($rules);

        return true;
    }

    /**
    * delete experiment to DB
    * @param  string $id
    * @param  Database $db
    */
    public function delete($id, Database $db) {
        $db->remove($this->collection,$id);

        $responses = new Responses($id);
        $responses->dropCollection($db);

        return true;
    }

    /**
    * Return experiment as array
    */
    public function toArray() {
        return array(
            'id' => $this->getId(),
            'title' => $this->getTitle(),
            'body' => $this->getBody(),
            'input' => $this->getInput(),
            'error' => $this->getError(),
            'random' => $this->getRandom(),
            'created' => $this->getCreated(),
            'meta' => (int) $this->getMeta(),
            'rules' => $this->getRules(),
        );
    }

    public function toMongo() {
        //Get object as array
        $mongoDoc = $this->toArray();
        unset($mongoDoc['id']);
        //Replace primitive types with MongoObjects where appropiate
        if($this->id)
        {
            $mongoDoc['_id'] = new \MongoId($this->id);
        }

        $mongoDoc['expId'] = new \MongoId($this->getExpId());
        $mongoDoc['created'] = new \MongoDate($this->getCreated());

        return $mongoDoc;
    }
}
