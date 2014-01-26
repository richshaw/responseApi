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
     * @var string
     */
    protected $collection = 'experiment';

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
     * Get list of valid input keys
     * @return mixed
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * Set list of slides to randomise
     * @param  mixed $random
     */
    public function setInput($input)
    {
        $this->input = $input;
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

        $v->rule('required', ['title', 'body', 'input']);
        $v->rule('isRandom',['random']);
        $v->rule('isInput',['input']);

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
        $doc = array(
            'title' => $this->title,
            'body' => $this->body,
            'input' => $this->input,
            'random' => $this->random
        );

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
        $this->setRandom($doc['random']);

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
}
