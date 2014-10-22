<div id="tribe-app-shop" class="wrap">
	<?php
	$html = trim( $html );
	if ( empty( $html ) ):
	?>
		<h3> Tribe App Store </h3>
		<p> Some default stuff if we failed to load fresh content </p>
	<?php
	else:
		echo $html;
	endif;	?>
</div>
