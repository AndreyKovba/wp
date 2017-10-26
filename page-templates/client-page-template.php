<?php
/**
 * Template Name: Client Page Template
 *
 * @package WordPress
 * @subpackage Twenty_Seventeen
 * @since 1.0
 */

//unset($_SESSION['client_group']);
$isBadGroupPassword = isset($_SESSION['bad-group-password']) ?: false;
unset($_SESSION['bad-group-password']);

function is_page_visible(){
    return true;
}

if(isset($_POST['group-password'])){
    $posts = get_posts([
        'post_type'		=> 'client-groups',
        'meta_query'	=> [
            [
                'key'	  	=> 'password',
                'value'	  	=> $_POST['group-password'],
                'compare' 	=> '=',
            ],
        ],
    ]);
    $url = '/';
    if(count($posts)){
        $_SESSION['client_group'] = $posts[0]->ID;
        $url = site_url() . '/welcome-page/';
    }
    else{
        unset($_SESSION['client_group']);
        $_SESSION['bad-group-password'] = true;
        if(isset($_POST['source_url'])) {
            $url = $_POST['source_url'];
        }
    }
    header('Location: ' . $url);
    exit;
}
else{
    get_header(); ?>

    <div id="primary" class="content-area">

        <main id="main" class="site-main" role="main">

            <?php
            if(!isset($_SESSION['client_group'])){
                ?>
                <div class="post-inner-content">
                    <?php
                    if($isBadGroupPassword){
                        ?>
                        <p style="color:red;">Incorrect password</p>
                        <?php
                    }
                    ?>
                    <p>This post is password protected. To view it please enter your password below:</p>
                    <form method="post" action="">
                        <input type="hidden" name="source_url" value="<?php echo $_SERVER["REQUEST_URI"]; ?>"/>
                        <div class="row">
                            <div class="col-lg-10">
                                <p>
                                    <label for="group-password">Password:</label>
                                </p>
                                <div class="input-group">
                                    <input class="form-control" value="" name="group-password" type="password"><br>
                                    <span class="input-group-btn">
                                        <input type="submit" class="btn btn-default" /><br>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <?php
            }
            else {
                if(!is_page_visible()){
                    ?>
                    <div class="post-inner-content">
                        this page is not opened for your group yet
                    </div>
                    <?php
                }
                else {
                    while (have_posts()) {
                        the_post();
                        get_template_part('template-parts/content', 'page');
                        // If comments are open or we have at least one comment, load up the comment template
                        if (get_theme_mod('sparkling_page_comments', 1) == 1) {
                            if (comments_open() || '0' != get_comments_number()) {
                                comments_template();
                            }
                        }
                    }
                }
            }
            ?>

        </main><!-- #main -->
    </div><!-- #primary -->

    <?php get_sidebar(); ?>
    <?php get_footer(); ?>
<?php
}
?>
