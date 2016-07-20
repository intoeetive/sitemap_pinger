<ul class="tab_menu" id="tab_menu_tabs">
<li class="content_tab"> <a href="<?=BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=sitemap_pinger'.AMP.'method=sitemaps'?>"><?=lang('sitemaps')?></a>  </li> 
<li class="content_tab current"> <a href="<?=BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=sitemap_pinger'.AMP.'method=engines'?>"><?=lang('search_engines')?></a>  </li> 

</ul> 
<div class="clear_left shun"></div> 


	<?php
		$this->table->set_template($cp_table_template);
		$this->table->set_heading($table_headings);
		echo $this->table->generate($data);
	?>


<?php
