<?php
class userModel{
	private $userId;
	private $email;
	private $age_group;
	private $gender;
	private $user_of_location;
    private $category_preference; //list of categories preffered
	
    //CONSTRUCTOR
    function addUser($userId, $email){
        $this->userId = $userId;
        $this->email = $email;
    }
    
    function addUserValues($userId,$email,$age_group,$gender,$user_of_location,$category_preference){
        $this->userId = $userId;
        $this->email = $email;
        $this->age_group = $age_group;
        $this->gender = $gender;
        $this->user_of_location = $user_of_location;
        $this->category_preference = $category_preference;
    }

    function addFromDB($userFromDB){
        $this->setUserId($userFromDB[1]['userId']);
        $this->setMail($userFromDB[1]['mail']);
        $this->setAgeGroup($userFromDB[1]['age_group']);
        $this->setGender($userFromDB[1]['gender']);
        $this->setLocation($userFromDB[1]['use_of_location']);
        if(is_array($userFromDB[2]))
            $this->setCategoryPrefs(explode(",",$userFromDB[2]['categories']));
    }

    //Print userModel as array for exporting
    public function printAll(){
        return array(
            'userId' => $this->getUserId(),
            'email' => $this->getMail(),
            'age_group' => $this->getAgeGroup(),
            'gender' => $this->getGender(),
            'user_of_location' => $this->getLocation(),
            'category_preference' => $this->getCategoryPrefs()
            );
    }

	//SETTERS
	public function setUserId($userId)
    {
        $this->userId = $userId;
    }
    
    public function setMail($email)
    {
        $this->email = $email;
    }
    
    public function setAgeGroup($age_group)
    {
        $this->age_group = $age_group;
    }
    
    public function setGender($gender)
    {
        $this->gender = $gender;
    }
    
    public function setLocation($user_of_location)
    {
        $this->user_of_location = $user_of_location;
    }

    public function setCategoryPrefs($category_preference)
    {
        $this->category_preference = $category_preference;
    }    

	//GETTERS
	public function getUserId()
    {
        return $this->userId;
    }
    
	public function getMail()
    {
        return $this->email;
    }
    
	public function getAgeGroup()
    {
        return $this->age_group;
    }
    
    public function getGender()
    {
        return $this->gender;
    }
    
	public function getLocation()
    {
        return $this->user_of_location;
    }

    public function getCategoryPrefs()
    {
        return $this->category_preference;
    }


}

?>