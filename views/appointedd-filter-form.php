<div class="wrapper">
    <div class="appointedd-filter-holder">
    <h3 class="appointedd-header atorge" >live celebrant availability checker</h3>
    <form class="appointedd-filter" name="appointed-filter-form" method="get" action="/meet-humanist-celebrant-scotland">
        <input type="hidden" value="10" name="limit" />
        <div class="form-group fusion-layout-column fusion-one-third">
            <label for="service">Service</label>
            <select name="service" class="custom-select sources" placeholder="Select Ceremony Type" <?php echo !empty($ceremony) && $ceremony != "all" ? "disabled-dropdown='true'" : ""; ?>>
            <option ></option>
            <?php

            foreach($services as $service){
				$key = $service->id;
                $display = $service->display;
                $selected = false;
                if($key == $ceremony){
                    $selected = true;
                }
                ?>
                <option value="<?php echo $key;?>" <?php echo $selected ? "selected" : ""; ?>><?php echo $display;?></option>
            <?php
            }

            ?>  
            </select>
        </div>
        <div class="form-group fusion-layout-column fusion-one-third">
            <label for="date">Date</label>
            <input type="text" name="display-date" onkeypress="return false;"  class="form-control app-display-date" placeholder="dd/mm/yyyy"/>
            <input type="hidden" name="date"  class="app-date" />
        </div>
        <div class="form-group fusion-layout-column fusion-one-third">
            <label for="location">Location of Ceremony</label>
            <select name="location" class="custom-select sources" placeholder="Select Location">
            <option ></option>
            <?php

            foreach($locations as $key => $location){
                ?>
                <option value="<?php echo $key;?>"><?php echo $location;?></option>
            <?php
            }

            ?>   
            </select>
        </div>

        <input type="submit" id="appointedd-submit-button" class="fusion-button button-flat fusion-button-round button-large button-default button-1" value="Search" />
        <input type="button" id="appointedd-enquire-button" class="fusion-button button-flat fusion-button-round button-large button-default button-1 clear" value="Enquire" />
    </form>
    </div>
    <div class="filter-results"></div>
    <div class="filter-results-paged pagination-sm"></div>
</div>
