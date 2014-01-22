<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Portal extends CI_Controller
{
    /**
     * Default page of the portal, redirects to Home
     */
    public function index()
    {
        //If the user is logged, render a custom home
        if ($this->_is_logged())
        {
            //Creating a user object to send to the view
            $user = $this->_create_session_user();
            
            //Load the home view for this $user
            $this->_load_home($user);
        }
        //Else render the visitors home page
        else
        {
            $this->_load_home();
        }
    }
    
    /**
     * Loads the User Profile page
     * @param type $user_id Id of the user whose profile will be shown
     */
    public function profile($user_id="")
    {
        //load user model
        $this->load->model('user_model');
        //load address model
        $this->load->model('address_model');
        
        //If theres no specified user, or something different from a number...
        if ($user_id === "" OR (intval($user_id) < 0))
        {               
            show_404();
        }
        //If there's a username parameter, let's try it
        else
        {
            //Parses the parameter to Int
            $user_id = intval($user_id);
            
            //Searches for the user, if not found, returns empty array
            $user = $this->user_model->get_user_by_id($user_id); 
            
            //Passes the user to the profile view
            $this->_load_profile($user);
        }
    }
    
    /**
     * Loads the authentication page
     */
    public function login()
    {   
        //If the user is logged in, he should not be registering
        //get out of here
        $this->_is_logged_redirect();
        
        //Validating the form
        //If there's something wrong with the data or no data, show the login page
        if($this->form_validation->run() === FALSE)
        {            
            $this->_load_login();      
        }else
        {
            $this->load->model('user_model');
            
            //At this point the form has valid data.            
            $pass = $this->input->post('password'); 
            $username = $this->input->post('username'); 
        
            //Trying to login
            $user = $this->user_model->login($username,$pass);            
                
            //if the login failed
            if ($user === array())
            {
                $data = $this->user_model->get_messages();
                $this->_load_login($data);        
            }
            //if the login matched
            else
            {    
                $this->_logged_on($user);               
            }
                
            
        }
        
    }
    
    /**
     * Logout method. Destroys session and redirect to home
     */
    public function logout(){
        $this->session->sess_destroy();
        redirect('/');
    }
    
    /**
     * Renders the registration form page if the user is not logged
     */
    public function register()
    {
        //Loading user model 
        $this->load->model('user_model');
        $this->load->model('address_model');
        
        //If the user is logged in, he should not be registering
        //get out of here
        $this->_is_logged_redirect();        
        
        //Getting the hidden post variable showAddress
        //that indicates if the user sent address data or not
        $showAddress = $this->input->post('showAddress'); //hidden input
        
        //the default validation rule will also validate the address info
        //If you change it to register_no_addr, it will ignore address info
        $validationRule = '';
        
        //If the user did not send address info
        if(isset($showAddress) && $showAddress === 'FALSE'){
            //Use the register_no_addr rule instead of default registration rule
            $validationRule = 'noAddress';
        }

        //Validating form data: 
        if($this->form_validation->run($validationRule) === FALSE)
        {            
            $this->_load_register_view();
        }
        else
        {
            //Gathering user data
            $name = $this->input->post('name');
            $last_name = $this->input->post('last_name');
            $email = $this->input->post('email');
            $password = $this->input->post('password');
            
            
            $added_id = null;
            if($showAddress !== 'FALSE'){
                //Gatherin user address
                $address = $this->input->post('addr_name');
                $addr_type = $this->input->post('addr_type');            
                $addr_additional = $this->input->post('addr_addition');
                $addr_number = $this->input->post('addr_number');
                $addr_city = $this->input->post('addr_city');
                $addr_state = $this->input->post('addr_state');
                $addr_country = $this->input->post('addr_country');
                $addr_postal = $this->input->post('addr_postal');
                
                //Trying to add the user address... 
                $added_id = $this->address_model->add_address($address,$addr_type,$addr_number,
                    $addr_additional,$addr_city,$addr_state,$addr_country,$addr_postal);
            }
            
            
            //If there was an error trying to add the address...
            if ($added_id === -1 OR $added_id === -2)
            {   
                //there's something wrong
                $this->_load_register_view(array('error'=>'PAU:'.$added_id));
            }
            //If the address was successfully added, let's try to add the User
            else
            {
                //Try adding the user to database
                $this->user_model->add_user($name,$last_name,$email,$password,$added_id);

                //Gather further info from model
                $data = $this->user_model->get_messages();

                //Loads 
                $this->_load_home($data);
            }
            
        }
        
    }
    
    
    public function searchProperty($data=array())
    {
        $this->load->model('property_model');
        if( $data !== array() )
        {
            if($this->form_validation->run() === FALSE)
            {            
                $this->_load_register_view();
            }
            else
            {                
                $data = $this->property_model->searchProperty($data);
                $this->_load_searchProperty($data);
            }
            
        }
        $this->_load_searchProperty($data);
    }
    
    public function _load_searchProperty()
    {
        $this->_load_header();
        $this->load->view('searchProperty_view');
        $this->_load_footer();
    }
    
    /**
     * Sets the style for the validation errors messages
     */
    public function _set_validation_error_style()
    {
        $this->form_validation->set_error_delimiters('<p class="errorDiv">', '</p>'); 
    }
    
    /**
     * Loads the page header. 
     * Optional parameter: Session User
     * @param type $data
     */
    public function _load_header($data=array())
    {        
        //Initializing template parameters
        $data = array(
          'home' => 'Home'  ,
            'profile' => 'Profile',
            'about' => 'About',
            'product_name' => 'Portal',
            'account' => 'Account',
            'sign_up' => 'Sign Up',
            'login' => 'Login',
            'logout' => 'Logout',
            'header_title' => 'Portal'
        );
        
        //Parse the template and render it
        $this->parser->parse('header',$data);
    }
    
    /**
     * Loads the page footer. 
     * Optional parameter: Session User
     * @param type $data
     */
    public function _load_footer($data=array())
    {
        $this->load->view('footer', $data);
    }
    
    /**
     * Loads the Home Page of the portal
     * Optional parameter: Session User
     * @param type $data
     */
    public function _load_home($data=array())
    {   
        $this->_load_header();
        $this->load->view('home_view',$data);
        $this->_load_footer();
    }
    
    /**
     * Loads the Login Page of the portal
     * Optional parameter: Session User
     * @param type $data
     */
    public function _load_login($data=array())
    {
        $data = array_merge($data,array(
            'form_title' => 'Enter Credentials',
            'username' => 'Email Address', 
            'password' => 'Password',
            'sign_in' => 'Sign In'
        ));
        
        $this->_set_validation_error_style();
        $this->_load_header();
        $this->parser->parse('login_view',$data); 
        $this->_load_footer();
    }
    
    /**
     * Loads the Register New User Page of the portal
     * Optional parameter: Session User
     * @param type $data
     */
    public function _load_register_view($data=array())
    {
        //Setting template values on the fly, we could load from a locale file
        //todo: create a locale file for template values
        $data = array_merge($data, array(
            'form_title' => 'Portal Sign Up',
            'name' => 'Name',
            'last_name' => 'Last Name',
            'email' => 'Email Address',
            'confirm_email' => 'Confirm Email Address',
            'password' => 'Password',
            'confirm_password' => 'Confirm Password',
            'address_panel' => 'Address Information',
            'address_type' => 'Address Type',
            'address' => 'Address',
            'number' => 'Number',
            'add_info' => 'Complement',
            'city' => 'City',
            'state' => 'State',
            'country' => 'Country',
            'zip' => 'Zip/Postal Code',
            'show_address_toggle' => '+Address >>',
            'showAddress' => 'FALSE'
        ));
        $this->_set_validation_error_style();
        $this->_load_header();
        $this->parser->parse('register_view',$data);
        $this->_load_footer();
    }
    
    /**
     * Loads the Successful Login View of the portal
     * (this view automatically redirects to user's profile)
     * Optional parameter: Session User
     * @param type $data
     */
    public function _load_logged_view($data=array())
    {
        $this->_load_header();
        $this->load->view('logged_view',$data);
        $this->_load_footer();
    }
    
    /**
     * Loads the Profile View for given user
     * @param type $data user whose profile will be rendered
     */
    public function _load_profile($data=array())
    {
        $this->_load_header();
        $this->load->view('profile_view',$data);
        $this->_load_footer();
    }
    
    /**
     * After a successful login, this method prepares
     * the session variables and redirects to the view
     */
    public function _logged_on($user)
    {       
        //Sets session variables for identification
        $this->session->set_userdata('logged',TRUE);
        $this->session->set_userdata('user_id',$user['id']);
        $this->session->set_userdata('username',$user['username']);
        $this->session->set_userdata('name',$user['name']);
        $this->session->set_userdata('last_name',$user['last_name']);
        
        $user = array(
            'username'  =>  $user['username'],
            'user_id'   =>  $user['id'],
            'name' => $user['name'],
            'last_name' => $user['last_name']
        );
        
        //Loads the view
        $this->_load_logged_view($user);
    }
    
    /**
     * If the current user is logged, he'll be redirected to default page
     */
    public function _is_logged_redirect(){
        //If you're logged in, you should not be here...
        if ($this->_is_logged())
        {
            redirect('/');
        }
    }
    
    /**
     * Indicates if the current user is logged in
     * @return type boolean returns true if the user is logged in
     */
    public  function _is_logged()
    {
        return $this->session->userdata('logged');
    }
    
    
    /**
     * Creates an object from the user session parameters
     * @return array An object representation of the user 
     */
    public function _create_session_user()
    {
        $user = array(
            'user_id'   =>  $this->session->userdata('user_id'),
            'username'  =>  $this->session->userdata('username')
        );
        
        return $user;
    }
  
    
}


/* End of file portal.php */
/* Location: ./application/controllers/portal.php */