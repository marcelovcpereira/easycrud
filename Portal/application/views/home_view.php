<div id="container" class="principal">
  
    <?php      
    
    
        //If user is logged...
        if(isset($user_id))
        {
            echo "Welcome " . $username;             
        }
        //If it's a visitor or unauthenticated member
        else
        {
            
            //Start printing messages from the Model>
            if (isset($status))
            {
                echo $status;
            }
     
            
            if (isset($error))
            {
                echo $error;
            }
            //End of Prints messages from the Model
            
        }
     ?>
    
</div>