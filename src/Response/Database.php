<?php

namespace Response;

class Database
{

    public $db = NULL;

    public $mongoUri;

    public function __construct($mongoUri)
    {
        $this->connect($mongoUri);
    }


    public function __destruct()
    {
        $this->disconnect();
    }


    public function connect($mongoUri, $retry = 3)
    {
        try {
            $m = new \MongoClient($mongoUri); ////////TOD HANDLE ERROR BETTER [30-Mar-2014 00:58:14 UTC] PHP Fatal error:  Uncaught exception 'MongoConnectionException' with message 'Failed to connect to: paulo.mongohq.com:10014: Remote server has closed the connection' in /srv/data/web/vhosts/timedresponse.io/src/Response/Database.php:29 https://jira.mongodb.org/browse/PHP-854
            $url = parse_url($mongoUri);
            $dbName = preg_replace('/\/(.*)/', '$1', $url['path']);
            $this->db = $m->selectDB($dbName);
            return $this->db;
        } catch ( MongoConnectionException $e ) {
            //TODO Log exception
            //die('Error connecting to MongoDB server');
        } catch ( MongoException $e ) {
            die('Mongo Error: ' . $e->getMessage());
        } catch ( Exception $e ) {
            die('Error: ' . $e->getMessage());
        }

        //Automatically retry connection on fail
        if ($retry > 0) {
            return getMongoClient($seeds, $options, --$retry);
        }

        //The connection is definately fucked
        die('Error connecting to MongoDB server');
    }

    public function disconnect()
    {
        if ($this->db === null)
        {
            $this->db->close();
            $this->db = null;
        }

        return $this->db;
    }


    public function insert($collection, $document)
    {
        if(!isset($document['created']))
        {
            //Add timestamp if not given
            $document['created'] = new \MongoDate(strtotime('now'));
        }
        else
        {
            //Make MongoObject
            $document['created'] = new \MongoDate($document['created']);
        }

        $this->db->$collection->insert($document);
        return $document;
    }

    public function batchInsert($collection, $documents)
    {
        $this->db->$collection->batchInsert($documents);
        return $documents;
    }


    public function update($collection, $id, $document)
    {
        $update = array('$set' => $document);
        $this->db->$collection->update(array('_id' => new \MongoId($id)),$update,array('w' => 1));
        return $document;
    }

    public function findOne($collection, $id)
    {
        return $this->db->$collection->findOne(array('_id' => new \MongoId($id)));
    }

    public function find($collection, $query = array())
    {
        return $this->db->$collection->find($query);
    }

    public function remove($collection, $id)
    {
        return $this->db->$collection->remove(array('_id' => new \MongoId($id)));
    }

    public function removeAll($collection)
    {
        return $this->db->$collection->remove();
    }

    public function createCollection($collection)
    {
        return $this->db->createCollection($collection);
    }

    public function dropCollection($collection)
    {
        return $this->db->$collection->drop();
    }

}
