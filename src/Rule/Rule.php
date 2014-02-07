<?php
/**
Wrapper for rule in rules engine
*/
namespace Rule;

class Rule
{

     /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $for;

    /**
     * Get rule name
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set rule name
     * @param  string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get slides rule is for
     * @return string
     */
    public function getFor()
    {
        return $this->name;
    }

    /**
     * Set slides rule is for
     * @param  string $for
     */
    public function setFor($for)
    {
        $this->for = $for;
    }

    /**
     * Get rule type
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set rule type
     * @param  string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Params we're going to use to create the rule
     * @param  array $params
     */
    public function validate($params)
    {

    }

    /**
    * Return response as array
    */
    public function toArray() {
    }

    /**
    * Return as Mongo Doc ready for insert or update
    */
    public function toMongo() {
    }
}
