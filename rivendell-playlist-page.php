<?php get_header(); ?>

<div class="wrapper section medium-padding">
										
	<div class="section-inner">
	
		<div class="content fleft">
	
			<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
		
				<div class="post">
				
					<div class="post-header">
												
					    <h1 class="post-title"><?php the_title(); ?></h1>
					    				    
				    </div> <!-- /post-header -->

					<div class="post-content">
								                                        
						<?php the_content(); ?>
						
						<div class="clear"></div>
															            			                        
					</div> <!-- /post-content -->
									
				</div> <!-- /post -->
			
			<?php endwhile; endif; ?>

			<div class="clear"></div>

		</div> <!-- /content -->
		
		<?php get_sidebar(); ?>
		
		<div class="clear"></div>

	</div> <!-- /section-inner -->

</div> <!-- /wrapper -->
								
<?php get_footer(); ?>
