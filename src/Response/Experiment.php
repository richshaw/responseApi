<?php
namespace Response;

/**
* GMT experiment
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
    protected $collection = 'experiment';

    /**
     * @var timestamp
     */
    protected $created;

    /**
     * @var int
     * 0 = No meta data, 1 = URL, 2 = Data attribute
     */
    protected $meta = 0;

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
     * Params we're going to use to create the experiment
     * @param  mixed $params
     */
    public function validate($params)
    {
        $v = new \Valitron\Validator($params);

        $v->rule('required', array('meta'));
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
            //Don't change id
            unset($doc['_id']);
            //Don't change created
            unset($doc['created']);
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
        $this->setCreated($doc['created']->sec);
        $this->setMeta($doc['meta']);

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
            'created' => $this->getCreated(),
            'meta' => (int) $this->getMeta(),
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

        if($this->created) {
            $mongoDoc['created'] = new \MongoDate($this->getCreated());
        }

        return $mongoDoc;
    }
}
