<div class="wrap">
  <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
  <h1>Invitation Logs</h1>
  <form method="post">
    <input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>">
    <?php $table->search_box( 'search', 'search_id' ); ?>
    <?php $table->display();?> 
  </form>
</div>