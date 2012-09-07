<?php

/**
 * Represents a Group in QBank.
 * @author Björn Hjortsten
 * @copyright Kaigan 2012
 * @package QBankAPIWrapper
 */
class Group {
	
	/**
	 * The id of the group.
	 * @var int
	 */
	protected $id;
	
	/**
	 * The name of the group.
	 * @var string
	 */
	protected $name;
	
	/**
	 * The date and time of creation of the group.
	 * @var DateTime
	 */
	protected $created;
	
	/**
	 * The id of the user who created this group.
	 * @var int
	 */
	protected $createdBy;
	
	/**
	 * The date and time when the group was last updated.
	 * @var DateTime
	 */
	protected $updated;
	
	/**
	 * The id of the user who last updated this group.
	 * @var int
	 */
	protected $updatedBy;
	
	/**
	 * Creates a new instance.
	 * @param int $id The id
	 * @param string $name The name
	 * @param DateTime $created The date and time of creation
	 * @param int $createdBy The id of the user who created the group
	 * @param DateTime $updated The date and time of the last update
	 * @param int $updatedBy The id of the user who last updated the group
	 * @author Björn Hjortsten
	 * @return Group
	 */
	public function __construct($id, $name, DateTime $created, $createdBy, DateTime $updated, $updatedBy) {
		$this->id = (int)$id;
		$this->name = (string)$name;
		$this->created = $created;
		$this->createdBy = (int)$createdBy;
		$this->updated = $updated;
		$this->updatedBy = (int)$updatedBy;
	}
	
	/**
	 * Gets the id of the Group.
	 * @author Björn Hjortsten
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}
	
	/**
	 * Gets the name of the Group.
	 * @author Björn Hjortsten
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}
	
	/**
	 * Gets the date and time of creation.
	 * @author Björn Hjortsten
	 * @return DateTime
	 */
	public function getCreated() {
		return $this->created;
	}
	
	/**
	 * Gets the id of the user who created the Group.
	 * @author Björn Hjortsten
	 * @return int
	 */
	public function getCreatedBy() {
		return $this->createdBy;
	}
	
	/**
	 * Gets the date and time of the last update.
	 * @author Björn Hjortsten
	 * @return DateTime
	 */
	public function getUpdated() {
		return $this->updated;
	}
	
	/**
	 * Gets the id of the user wh last updated the Group.
	 * @author Björn Hjortsten
	 * @return int
	 */
	public function getUpdatedBy() {
		return $this->updatedBy;
	}
	
	/**
	 * Creates a Group from an object directly from a call to the API.
	 * WARNING: If this is called with the wrong raw object, you may get warnings or even errors!
	 * @param stdClass $rawObject
	 * @author Björn Hjortsten
	 * @return Group
	 */
	public static function createFromRawObject($rawObject) {
		try {
			$created = new DateTime($rawObject->created);
		} catch (Exception $e) {
			$created = new DateTime('0000-01-01 00:00:00');
		}
		try {
			$updated = new DateTime($rawObject->updated);
		} catch (Exception $e) {
			$updated = new DateTime('0000-01-01 00:00:00');
		}
		return new Group(
			$rawObject->id,
			$rawObject->name,
			$created,
			$rawObject->created_by,
			$updated,
			$rawObject->updated_by
		);
	}
	
	/**
	 * Returns the basic information of this object as a human readable string.
	 * @author Björn Hjortsten
	 * @return string
	 */
	public function __toString() {
		return sprintf(
			'[Group id:%d name:%s created:%s created by:%d updated:%s updated by:%d',
			$this->id,
			$this->name,
			$this->created->format('Y-m-d H:i:s'),
			$this->createdBy,
			$this->updated->format('Y-m-d H:i:s'),
			$this->updatedBy
		);
	}
}
?>