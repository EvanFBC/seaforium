<?php $favorite = in_array($thread_id, $favorites) ? ' added' : ''; ?>	
				<div id="thread">
					<div id="main-title">
						<h3><?php echo $info['title'] ?></h3>
						<?php if ($this->sauth->is_logged_in()) { ?><a class="favourite<?php echo $favorite; ?>" rel="<?php echo $thread_id; ?>"></a><?php } ?>
					</div>
					
					<div class="pagination top">
						<?php echo $pagination; ?>
					</div>
				
<?php

$i = 0;

// loop through and print out all the comments
foreach($comment_result->result() as $row) { 

	// if the comment has been deleted,
	// go ahead and just put the deleted thing and start the loop over
	if ($row->deleted == '1')
	{
?>

					<div class="comment deleted">
						comment by <?php echo $row->username; ?> deleted
					</div>
<?php
		continue;
	}
	
	// alternating comment colors!!!
	$alt = (!isset($alt) || $alt === false ? true : false);
	
	$my_thread = $row->user_id == $this->session->userdata('user_id');
	
	$edit_source = ($my_thread && strtotime($row->created) > time() - 3600) ? 'edit' : 'View Source';
	
	$url_safe_username = url_title($row->username, 'dash', TRUE);
	
	switch($row->acq_type)
	{
		case 1:
			$acq = ' buddy';
			break;
		case 2:
			$acq = ' enemy';
			break;
		default:
			$acq = null;
	}
	
	if ($row->acq_type == 2)
	{
?> 

					<div id="ignore-for-<?php echo $row->comment_id; ?>" class="ignore-container" onclick="$('#comment-container-<?php echo $row->comment_id; ?>').toggle();"></div>

<?php } ?> 
					<div id="comment-<?php echo $row->comment_id; ?>" class="comment<?php echo $alt === false ? '' : ' alt'; echo $acq; ?>">
						<div id="comment-container-<?php echo $row->comment_id; ?>" class="comment-container">
							<div class="cmd-bar">
								<span><a class="view-source" onclick="thread.view_source(<?php echo $row->comment_id; ?>); return false;"><?php echo $edit_source; ?></a></span>
								<?php if ($this->sauth->is_logged_in()) { ?><a class="quote" onclick="thread.quote(<?php echo $row->comment_id; ?>);">Quote</a><?php } ?> 
							</div>
							<div class="user-block">
								<div class="username<?php echo $acq; ?>"><?php echo anchor('/user/'. $url_safe_username, $row->username); ?></div>
								<div class="time"><?php echo timespan(strtotime($row->created), time()) ?></div>
								
								<div class="user-information" style="background: url(/img/noavatar.gif);">
									<ul>
									<?php if ($this->sauth->is_logged_in()) { ?> 
										<li><a href="/buddies/<?php echo $url_safe_username; ?>"><?php echo ($acq)? "Your $acq!" : 'BUDDY? ENEMY?'; ?></a></li>
										<li><a href="/messages/send/<?php echo $url_safe_username; ?>">SEND A MESSAGE</a></li>
									<?php } else { ?> 
										<li>&nbsp;</li>
										<li>&nbsp;</li>
									<?php } ?> 
									</ul>
								</div>
								
								<?php if ($my_thread && $i === 0 && $starting === 0) { 
								
									$session_id = $this->session->userdata('session_id');
									
									$set_nsfw_status = $info['nsfw'] === '1' ? 0 : 1;
									$set_closed_status = $info['closed'] === '1' ? 0 : 1;
									
									$nsfw_text = $info['nsfw'] === '1' ? 'Unmark Naughty' : 'Mark Naughty';
									$closed_text = $info['closed'] === '1' ? 'Open Thread' : 'Close Thread';
								
								?>
								<div id="thread-control">
									<p>THREAD ADMIN</p>
									<ul>
										<li id="control-nsfw">&middot; <span><?php echo $nsfw_text; ?></span></li>
										<li id="control-closed">&middot; <span><?php echo $closed_text; ?></span></li>
									</ul>
								</div>
								
								<script type="text/javascript">
									$('#control-nsfw span').bind('click', function(){
										thread.set_status(<?php echo $row->thread_id; ?>, 'nsfw', <?php echo $set_nsfw_status; ?>, '<?php echo $session_id; ?>')
									});
									$('#control-closed span').bind('click', function(){
										thread.set_status(<?php echo $row->thread_id; ?>, 'closed', <?php echo $set_closed_status; ?>, '<?php echo $session_id; ?>');
									});
								</script>
								<?php }	?>
								
							</div>
							<div class="content-block">
								<div class="content">
<?php 
echo $this->session->userdata('view_html') === '1' || $this->session->userdata('view_html') === false 
	? _ready_for_display($row->content, array('username'=>$row->username, "url_safe_username"=>$url_safe_username)) 
	: nl2br(htmlentities($row->content));
?>
								</div>
							</div>
							<div style="clear: both;"></div>
						</div>
					</div>
<?php ++$i; } ?>

					<div class="pagination bottom">
						<?php echo $pagination; ?>
					</div>

					<div class="dotted-bar replypad"></div>
<?php if (!$this->sauth->is_logged_in() || $info['closed'] === '1' || $info['acq_type'] === 2) { 
	
	if ($info['closed'] === '1')
	{
	?>
	
					<h3>This thread is closed</h3>
	
	<?php
	}
	elseif ($info['acq_type'] === 2)
	{
	?>
	
					<h3>You cannot post in this thread</h3>
	
	<?php
	}
	
	
	
}
else
{

// and now the reply form
$content = array(
	'name'	=> 'content',
	'id'	=> 'thread-content-input',
	'value' => set_value('content')
);

?>
					
					
					<div id="reply-lc">
						
						<h4>Post A Reply</h4>
						
						<div id="post-shortcuts">
							<p>SHORTCUTS!</p>
							<ul>
								<li>&middot; <a href="javascript:insertAtCaret('thread-content-input', '<a href=%22%22></a>')">URL</a></li>
								<li>&middot; <a href="javascript:insertAtCaret('thread-content-input', '<img src=%22%22 />')">Image</a></li>
								<li>&middot; <a href="#">Spoiler</a></li>
							</ul>
						</div>
						
					</div>
					
					<div id="reply-rc">
						
						<div id="pinkies">
							<a href="javascript:insertAtCaret('thread-content-input', '[:)]');"><img src="/img/pinkies/11.gif" /></a>
							<a href="javascript:insertAtCaret('thread-content-input', '[:(]');"><img src="/img/pinkies/01.gif" /></a>
							<a href="javascript:insertAtCaret('thread-content-input', '[:D]');"><img src="/img/pinkies/05.gif" /></a>
							<a href="javascript:insertAtCaret('thread-content-input', '[;)]');"><img src="/img/pinkies/07.gif" /></a>
							<a href="javascript:insertAtCaret('thread-content-input', '[:P]');"><img src="/img/pinkies/08.gif" /></a>
							<a href="javascript:insertAtCaret('thread-content-input', '[>|]');"><img src="/img/pinkies/14.gif" /></a>
							<a href="javascript:insertAtCaret('thread-content-input', '[:[]');"><img src="/img/pinkies/10.gif" /></a>
							<a href="javascript:insertAtCaret('thread-content-input', '[\'(]');"><img src="/img/pinkies/03.gif" /></a>
							<a href="javascript:insertAtCaret('thread-content-input', '[:*]');"><img src="/img/pinkies/17.gif" /></a>
							<a href="javascript:insertAtCaret('thread-content-input', '[B-]');"><img src="/img/pinkies/16.gif" /></a>
							<a href="javascript:insertAtCaret('thread-content-input', '[:=]');"><img src="/img/pinkies/27.gif" /></a>
							<a href="javascript:insertAtCaret('thread-content-input', '[:.]');"><img src="/img/pinkies/22.gif" /></a>
							<a href="javascript:insertAtCaret('thread-content-input', '[O]');"><img src="/img/pinkies/24.gif" /></a>
							<a href="javascript:insertAtCaret('thread-content-input', '[8)]');"><img src="/img/pinkies/09.gif" /></a>
							<a href="javascript:insertAtCaret('thread-content-input', '[:{]');"><img src="/img/pinkies/06.gif" /></a>
							<a href="javascript:insertAtCaret('thread-content-input', '[:@]');"><img src="/img/pinkies/20.gif" /></a>
							<a href="javascript:insertAtCaret('thread-content-input', '[%(]');"><img src="/img/pinkies/18.gif" /></a>
							<a href="javascript:insertAtCaret('thread-content-input', '[><]');"><img src="/img/pinkies/25.gif" /></a>
							<a href="javascript:insertAtCaret('thread-content-input', '[RR]');"><img src="/img/pinkies/23.gif" /></a>
							<a href="javascript:insertAtCaret('thread-content-input', '[NH]');"><img src="/img/pinkies/26.gif" /></a>
							<a href="javascript:insertAtCaret('thread-content-input', '[fbm]');"><img src="/img/pinkies/21.gif" /></a>
						</div>
						
						<form method="post" action="<?php echo uri_string(); ?>" id="comment-form">
							
							<div class="input textarea">
								<?php echo form_textarea($content); ?> 
							</div>
							
							<p>I, <?php echo $this->session->userdata('username'); ?>, do solemnly swear that in posting this comment I promise to be nice.</p>
							
							<button type="submit" id="submit-button">Agree &amp; Post</button>
						</form>
						
					</div>

					<div id="notifications">
						<a id="closenotify"></a>
					</div>                                                                                    

<?php } ?> 
					<script type="text/javascript" src="/js/thread.js"></script>
					<script type="text/javascript">
						thread_id = <?php echo $thread_id; ?> 
						total_comments = <?php echo $total_comments; ?> 
						notification = setInterval("thread_notifier()",10000);
					</script>
					
					<script type="text/javascript">
						session_id = '<?php echo $this->session->userdata('session_id'); ?>';

						$('.favourite').bind('click', function(){
							button = $(this);

							if (!$(this).hasClass('added'))
							{
								$.get(
								'/ajax/favorite_thread/'+ $(this).attr('rel') +'/'+ session_id,
								function(data) {
									if (data == 1) button.addClass('added');
								}
								);
							}
							else
							{
								$.get(
								'/ajax/unfavorite_thread/'+ $(this).attr('rel') +'/'+ session_id,
								function(data) {
									if (data == 1) button.removeClass('added');
								}
								);
							}

							return;
						});
					</script>
					
				</div>