<?php

function wpalfa_people_page_handler()
{
    global $wpdb;

    $table = new Custom_Table_Alfa_List_Table();
    $table->prepare_items();

    $message = '';
    if ('delete' === $table->current_action()) {
        $message = '<div class="updated below-h2" id="message"><p>' . sprintf(__('Items deleted: %d', 'wpalfa'), count($_REQUEST['id'])) . '</p></div>';
    }
    ?>
<div class="wrap">
    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
    <h2><?php _e('People', 'wpalfa')?> <a class="add-new-h2"
                                 href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=people_form');?>"><?php _e('Add new Person', 'wpalfa')?></a>
    </h2>
    <?php echo $message; ?>

    <form id="people-table" method="POST">
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
        <?php $table->display() ?>
    </form>

</div>
<?php
}

function wpalfa_contacts_page_handler()
{
    global $wpdb;

    $table = new Custom_Table_Alfa_Contact_List_Table();
    $table->prepare_items();

    $message = '';
    if ('delete' === $table->current_action()) {
        $message = '<div class="updated below-h2" id="message"><p>' . sprintf(__('Items deleted: %d', 'wpalfa'), count($_REQUEST['id'])) . '</p></div>';
    }
    ?>
<div class="wrap mt-1">
    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
    <h2><?php _e('Contact', 'wpalfa')?> <a class="add-new-h2"
                                 href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=contacts_form');?>"><?php _e('Add new Contact', 'wpalfa')?></a>
    </h2>
    <?php echo $message; ?>

    <form id="contacts-table" method="POST">
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
        <?php $table->display() ?>
    </form>

</div>
<?php
}

function wpalfa_people_form_page_handler()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'alfapessoas'; 

    $message = '';
    $notice = '';


    $default = array(
        'id' => 0,
        'name'      => '',
        'email'     => '',
    );


    if ( isset($_REQUEST['nonce']) && wp_verify_nonce($_REQUEST['nonce'], basename(__FILE__))) {
        
        $item = shortcode_atts($default, $_REQUEST);     

        $item_valid = wpalfa_validate_person($item);
        if ($item_valid === true) {
            if ($item['id'] == 0) {
                $result = $wpdb->insert($table_name, $item);
                $item['id'] = $wpdb->insert_id;
                if ($result) {
                    $message = __('Item was successfully saved', 'wpalfa');
                } else {
                    $notice = __('There was an error while saving item', 'wpalfa');
                }
            } else {
                $result = $wpdb->update($table_name, $item, array('id' => $item['id']));
                if ($result) {
                    $message = __('Item was successfully updated', 'wpalfa');
                } else {
                    $notice = __('There was an error while updating item', 'wpalfa');
                }
            }
        } else {
            
            $notice = $item_valid;
        }
    }
    else {
        
        $item = $default;
        if (isset($_REQUEST['id'])) {
            $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $_REQUEST['id']), ARRAY_A);
            if (!$item) {
                $item = $default;
                $notice = __('Item not found', 'wpalfa');
            }
        }
    }

    
    add_meta_box('contacts_form_meta_box', __('Person data', 'wpalfa'), 'wpalfa_people_form_meta_box_handler', 'contact', 'normal', 'default');

    ?>
<div class="wrap">
    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
    <h2><?php _e('Person', 'wpalfa')?> <a class="add-new-h2" href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=contacts');?>"><?php _e('back to list', 'wpalfa')?></a>
    </h2>

    <?php if (!empty($notice)): ?>
    <div id="notice" class="error"><p><?php echo $notice ?></p></div>
    <?php endif;?>
    <?php if (!empty($message)): ?>
    <div id="message" class="updated"><p><?php echo $message ?></p></div>
    <?php endif;?>

    <form id="form" method="POST">
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(basename(__FILE__))?>"/>
        
        <input type="hidden" name="id" value="<?php echo $item['id'] ?>"/>

        <div class="metabox-holder" id="poststuff">
            <div id="post-body">
                <div id="post-body-content">
                    
                    <?php do_meta_boxes('contact', 'normal', $item); ?>
                    <input type="submit" value="<?php _e('Save', 'wpalfa')?>" id="submit" class="button-primary" name="submit">
                </div>
            </div>
        </div>
    </form>
</div>
<?php
}


function wpalfa_contacts_form_page_handler()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'alfacontatos'; 

    $message = '';
    $notice = '';


    $default = array(
        'id' => 0,
        'country_code' => '',
        'phone'  => '',
    );


    if ( isset($_REQUEST['nonce']) && wp_verify_nonce($_REQUEST['nonce'], basename(__FILE__))) {
        
        $item = shortcode_atts($default, $_REQUEST);     

        $item_valid = wpalfa_validate_contact($item);
        if ($item_valid === true) {
            if ($item['id'] == 0) {
                $result = $wpdb->insert($table_name, $item);
                $item['id'] = $wpdb->insert_id;
                if ($result) {
                    $message = __('Item was successfully saved', 'wpalfa');
                } else {
                    $notice = __('There was an error while saving item', 'wpalfa');
                }
            } else {
                $result = $wpdb->update($table_name, $item, array('id' => $item['id']));
                if ($result) {
                    $message = __('Item was successfully updated', 'wpalfa');
                } else {
                    $notice = __('There was an error while updating item', 'wpalfa');
                }
            }
        } else {
            
            $notice = $item_valid;
        }
    }
    else {
        
        $item = $default;
        if (isset($_REQUEST['id'])) {
            $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $_REQUEST['id']), ARRAY_A);
            if (!$item) {
                $item = $default;
                $notice = __('Item not found', 'wpalfa');
            }
        }
    }    
    add_meta_box('contacts_form_meta_box', __('Contact data', 'wpalfa'), 'wpalfa_contacts_form_meta_box_handler', 'contact', 'normal', 'default');

    ?>
<div class="wrap">
    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
    <h2><?php _e('Contact', 'wpalfa')?> <a class="add-new-h2" href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=contacts');?>"><?php _e('back to list', 'wpalfa')?></a>
    </h2>

    <?php if (!empty($notice)): ?>
    <div id="notice" class="error"><p><?php echo $notice ?></p></div>
    <?php endif;?>
    <?php if (!empty($message)): ?>
    <div id="message" class="updated"><p><?php echo $message ?></p></div>
    <?php endif;?>

    <form id="form" method="POST">
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(basename(__FILE__))?>"/>
        
        <input type="hidden" name="id" value="<?php echo $item['id'] ?>"/>

        <div class="metabox-holder" id="poststuff">
            <div id="post-body">
                <div id="post-body-content">
                    
                    <?php do_meta_boxes('contact', 'normal', $item); ?>
                    <input type="submit" value="<?php _e('Save', 'wpalfa')?>" id="submit" class="button-primary" name="submit">
                </div>
            </div>
        </div>
    </form>
</div>
<?php
}

function wpalfa_people_form_meta_box_handler($item)
{
    ?>
<tbody >
		
	<div class="formdatabc">		
		
    <form >
		<div class="form2bc">
        <p>			
		    <label for="name"><?php _e('Name:', 'wpalfa')?></label>
		<br>	
            <input id="name" name="name" type="text" value="<?php echo esc_attr($item['name'])?>"
                    required>
		</p>
		</div>	
		<div class="form2bc">
			<p>
            <label for="email"><?php _e('E-Mail:', 'wpalfa')?></label> 
		<br>	
            <input id="email" name="email" type="email" value="<?php echo esc_attr($item['email'])?>"
                   required>
        </p>
		</div>	
		</form>
		</div>
</tbody>
<?php
}

function wpalfa_contacts_form_meta_box_handler($item)
{
    ?>
<tbody >


	<div class="formdatabc">		
		
    <form >
		<div class="form2bc">
        <p>			
		    <label for="pais"><?php _e('País:', 'wpalfa')?></label>
            <div class="container country-container">
                <select class="countries" name="countries" id="countries"></select>
            </div>
        </p>

		<br>
        </div>
        <div class="form2bc">
        <p>			
		    <label for="country_code"><?php _e('country code:', 'wpalfa')?></label>
		<br>	
            <input id="country_code" name="country_code" type="text" value="<?php echo esc_attr($item['country_code'])?>"
                    required>
		</p>
		</div>	
		<div class="form2bc">
			<p>
            <label for="phone"><?php _e('Phone:', 'wpalfa')?></label> 
		<br>	
            <input id="phone" name="phone" type="phone" value="<?php echo esc_attr($item['phone'])?>"
                   required>
        </p>
		</div>	
		</form>
		</div>
</tbody>
<script>
        const xhttp = new XMLHttpRequest();
        const select = document.getElementById("countries");

        let countries;

        xhttp.onreadystatechange = function () {
        console.log('this.status', this.status);
        if (this.readyState == 4 && this.status == 200) {
            countries = JSON.parse(xhttp.responseText);
            assignValues();
            handleCountryChange();
        }
        };
        xhttp.open("GET", "https://restcountries.com/v3.1/all", true);
        xhttp.send();

        function assignValues() {
        let sorted = countries.sort(function(a,b){
            return  b.name.common > a.name.common;
        })
        sorted.forEach(country => {
            const option = document.createElement("option");
            option.value = (country.idd.root? country.idd.root.replace(/\+/,""): "") + country.idd.suffixes;
            option.textContent = country.name.common + ' ('+ (country.idd.root? country.idd.root.replace(/\+/,""): "") + country.idd.suffixes + ')';
            select.appendChild(option);
        });
        }

        function handleCountryChange() {
        const countryData = countries.find(
        country => select.value === country.alpha2Code);
        }

        select.addEventListener("change", function(e){console.log(e.target.value)});

</script>
<?php
}
