<!-- EXPECTED VARIABLES: $username, $user_id, $name, $last_name -->

<div id="container" >
    <div class="loggedDiv">
    <?php if (isset($username) && isset($user_id)){ ?>
    
        <?php
                $message = "You're successfully logged in {$name}, redirecting you to your profile.";
          
                echo anchor('portal/profile/'.$user_id,$message);
            
                header('Refresh:2;url='.  base_url() .'portal/profile/'.$user_id);
            }
                 
        ?>
    </div>
          
    
</div>


