<?php
require_once '../models/userModel.php';
require_once '../models/preferenceValue.php';
require_once '../database/dbStory.php';
require_once 'weights.php';

class computePreferenceValues {
	
	private $user;
	private $dbStory;
	private $latestStateTime;
	
	/*The input $user must be a userModel-instance*/
	public function __construct($user){
		$this->user = $user;
		$this->dbStory = new dbStory();
	}

	/*Compute preferences for all stories for this user*/
	public function computeAllValues(){
		$stories = $this->dbStory->getStories();
		foreach($stories as $story){
			$storyModel = new storyModel();
			$storyModel->setstoryId($story['storyId']);
			$storyModel->setCategoryList($story['categories']);
			$this->computeOneValue($storyModel);
		}
	}

	/**Compute the user's preference for the input $storyModel
	 *If calling this method directly, the storyModel need to have set the categoryList (and storyId) beforehand
	 */
	public function computeOneValue($storyModel){
		$rating = $this->dbStory->getSelected('stored_story', 'rating', array('storyId', 'userId'),array($storyModel->getstoryId(), $this->user->getUserId()));
		$storyModel->setRating($rating[0]['rating']);
		
		$preferenceValue = new preferenceValue($storyModel, $this->user);
		$this->setPreferenceValueVariables($preferenceValue);
		
		$value = $this->computePreferenceValue($preferenceValue);
		
		$this->dbStory->insertUpdateAll('preference_value', array($this->user->getUserId(), $storyModel->getstoryId(), $value));
	}
	
	/*Not sure if the weights are used correctly here */
	private function computePreferenceValue($preferenceValue){
		$value = 0;
		
		/*getCommonCategoryPercentage describe how similar the user's category preference is to a story's categories*/
		$value += CATEGORY_LIKE*$preferenceValue->getCommonCategoryPercentage();
		
		/*getNumToBeRead is how many times the story have been put in the to-be-read list*/
		$value += READ_LATER*$preferenceValue->getNumToBeRead();
		
		/*getNumRecommended is the number of times the story have been recommended. 
		  If a story have been recommended several times this should have a negative impact*/
		$value -= CATEGORY_NO_ANSWER*$preferenceValue->getNumRecommended();
		
		/*As of now, swiping is not stored in the database, so this will have no effect*/
		$value -= SWIPED_PAST*$preferenceValue->getNumSwipedPast();
		
		/*Not sure if this should be weighted. As it is now, it's dominating the value quite heavily. But maybe it should do that?*/
		$value += $preferenceValue->getRescoredRating();
		
		/*Mahout doesn't seem to like negative values, so 0 is the lowest possible value*/
		if ($value<0){
			$value = 0;
		}
		return $value;
	}
		
	/*Setting the variables for how often the states have occurred for this story by this user*/
	private function setPreferenceValueVariables($preferenceValue){
		$this->latestStateTime = null;
		$states = $this->dbStory->getStatesPerStory($preferenceValue->getUser()->getUserId(), $preferenceValue->getStory()->getstoryId());
		foreach($states as $row){
			$this->setStateVariable($row, $preferenceValue);
		}
		
		/*This if-statement decides whether a user has set a story to "Not interested" or not*/
		if(!is_null($this->latestStateTime)){
			/*If the user never has set the story to "Not interested"*/
			if(!array_key_exists(6, $this->latestStateTime)){
				$preferenceValue->setNotInterested(false);
			}
			/*If the user has set a story to "Not interested" at some point*/
			else {
				/*If the user has set the story to "Not interested" and never rated it*/
				if (!array_key_exists(5,$this->latestStateTime)){
					$preferenceValue->setNotInterested(true);
				}
				/*If the user has set the story to "Not interested" and rated it at some point*/
				else {
					/*If the last "Not interested" happened after the last rating, the user is not interested in this story*/
					if ($this->latestStateTime[6]>$this->latestStateTime[5]){
						$preferenceValue->setNotInterested(true);
					}
					/*If the last rating happened after the last "Not interested", the user should be considered interested in the story*/
					else{
						$preferenceValue->setNotInterested(false);
					}
				}
			}
		}
	}
	

	/*Helper function*/
	private function setStateVariable($row, $preferenceValue){
		$stateId = $row['stateId'];
		$numberOfOccurrences = $row['numTimesRecorded'];
		$this->latestStateTime[$stateId] = $row['latestStateTime'];
		switch ($stateId) {
			
			case 1:
				$preferenceValue->setNumRecommended($numberOfOccurrences);
			break;
			
			case 2:
				$preferenceValue->setNumRejected($numberOfOccurrences);
			break;
			
			case 3:
				$preferenceValue->setNumToBeRead($numberOfOccurrences);
			break;
			
			case 4:
				$preferenceValue->setNumRead($numberOfOccurrences);
			break;
		
			case 5:
				$preferenceValue->setNumRated($numberOfOccurrences);
			break;
						
			/*TODO: A SWIPED_PAST-case should be here*/
			
			default:
				echo "";
			break;
		}
	}
}
//$user = new userModel();
//$user->addUserValues(1, 'test', null, null, null, array(5,4,9));
//$c = new computePreferenceValues($user);
//$c->computeAllValues();
?>
