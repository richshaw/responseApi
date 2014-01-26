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


    public function connect($mongoUri)
    {
        if ($this->db === null)
        {
            try {
                $m = new \MongoClient($mongoUri);
            } catch ( MongoConnectionException $e ) {
                die('Error connecting to MongoDB server');
            } catch ( MongoException $e ) {
                die('Mongo Error: ' . $e->getMessage());
            } catch ( Exception $e ) {
                die('Error: ' . $e->getMessage());
            }

            $url = parse_url($mongoUri);
            $dbName = preg_replace('/\/(.*)/', '$1', $url['path']);
            $this->db = $m->selectDB($dbName);
        }

        return $this->db;
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
        $this->db->$collection->insert($document,array('w' => 1));
        return $document;
    }

    public function update($collection, $id, $document)
    {
        $this->db->$collection->update(array('_id' => new \MongoId($id)),$document,array('w' => 1));
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

    public function createCollection($collection)
    {
        return $this->db->createCollection($collection);
    }

    public function dropCollection($collection)
    {
        return $this->db->$collection->drop();
    }

}