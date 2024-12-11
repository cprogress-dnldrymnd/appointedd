<div class="profiles-holder">
    <div class="profiles">
        <?php
        foreach ($profile_ids as $profile) {
            $post = get_post($profile->post_id);
            $profile_name = get_the_title($post);
            $image = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'single-post-thumbnail')[0];
            $link =  get_permalink($post->ID);
            $book_link = "https://fuzeceremonies.appointedd.com/bookable/$profile->meta_value";
        ?>

            <div class="profile" style="display: none !important;">
                <div class="profile-inner">
                    <div class="profile-img">
                        <a href="<?php echo $link ?>">
                            <img src="<?php echo $image; ?>" />
                        </a>
                    </div>
                    <div class="profile-info-wrapper">
                        <div class="profile-info">
                            <div class="profile-name"><?php echo $profile_name; ?></div>
                        </div>
                        <div class="profile-actions">
                            <div class="profile-action-view button-box button-accent">
                                <a class="button" target="_self" href="<?php echo $link ?>"><span class="fusion-button-text">View Profile</span></a>
                            </div>
                            <div class="profile-action-book button-box button-bordered">
                                <a class="button" target="_self" href="<?php echo $book_link ?>"><span class="fusion-button-text">Book</span></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php
        }
        ?>
    </div>
</div>