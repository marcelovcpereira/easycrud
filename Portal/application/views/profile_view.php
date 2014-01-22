<!-- EXPECTED PARAMETERS: $username, $user_id,$name,$last_name-->
<div id="container" class="principal">
    
    
    <?php if (isset($user_id)){ ?>
    <!--If the user IS specified (the user whose profile is being seen -->        
        <h2><?=$name.' '.$last_name?></h2>
        
        <div id="profileDiv" class="perfil3colunas">
            
            <div class="coluna">
                <div class="title">Personal</div>
                <p>
                    <?php 
                        echo $name . $last_name ;
                        echo br(1);
                        echo $username;
                    ?>
                </p>
            </div>
            
            <div class="coluna">
                <div class="title">Address</div>
                <p>
                    <?php echo $address['type'] . ' ' . $address['name'] . ', ' . $address['number'];                  
                        echo "<br>" . $address['city'] . ', ';
                        echo $address['state'] . ' - ';
                        echo $address['country'];
                        echo "<br>Postal Code: " . $address['postal_code'];
                        echo "<br>" . $address['additional'];
                    ?>
                </p>
            </div>
            
        </div>
        
    <?php }else{ ?>
    <!--If the user is NOT specified -->        
    
    <div class="errorDiv">No user found.</div>
        
        
    <?php } ?>
          
    
</div>