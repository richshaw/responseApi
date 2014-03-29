<?php

namespace Response;

/**
* Loads multiple timed response experiments
*/
class Experiments
{

    /**
     * @var string
     */
    protected $collection = 'experiment';

    /**
    * Load multuple experiments from DB
    * @param  Database $db
    */
    public function load(Database $db) {
        $cursor = $db->find($this->collection);

        $experiments = array();
        foreach ($cursor as $doc) {
            $experiment = new \Response\Experiment();

            $experiment->setId((string) $doc['_id']);
            $experiment->setCreated($doc['created']->sec);
            $experiment->setMeta($doc['meta']);

            $experiments[] = $experiment;
        }

        return $experiments;
    }


}
