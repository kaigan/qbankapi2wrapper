<?php
	
class Group {
	protected $id;
	protected $name;
	protected $created;
	protected $createdBy;
	protected $updated;
	protected $updatedBy;
	
	public function __construct($id, $name, DateTime $created, $createdBy, DateTime $updated, $updatedBy) {
		$this->id = (int)$id;
		$this->name = (string)$name;
		$this->created = $created;
		$this->createdBy = (int)$createdBy;
		$this->updated = $updated;
		$this->updatedBy = (int)$updatedBy;
	}
	
	public function getId() {
		return $this->id;
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function getCreated() {
		return $this->created;
	}
	
	public function getCreatedBy() {
		return $this->createdBy;
	}
	
	public function getUpdated() {
		return $this->updated;
	}
	
	public function getUpdatedBy() {
		return $this->updatedBy;
	}
	
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