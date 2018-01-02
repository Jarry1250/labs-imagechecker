<?php
	/**
	 * Image Existence Checker © 2011-2014
	 * @author Harry Burt <jarry1250@gmail.com>
	 * @package ImageChecker
	 *
	 * Image Existence Checker is free software; you can redistribute it and/or modify
	 * it under the terms of the GNU General Public License as published by
	 * the Free Software Foundation; either version 2 of the License, or
	 * (at your option) any later version.
	 *
	 * Image Existence Checker is distributed in the hope that it will be useful,
	 * but WITHOUT ANY WARRANTY; without even the implied warranty of
	 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	 * GNU General Public License for more details.
	 *
	 * You should have received a copy of the GNU General Public License
	 * along with Image Existence Checker; if not, write to the Free Software
	 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
	 */

	require_once( '/data/project/jarry-common/public_html/global.php' );

	$lang = ( isset( $_GET['lang'] ) ) ? $_GET['lang'] : '';
	$category = ( isset( $_GET['category'] ) ) ? $_GET['category'] : '';
	$includePNGs = ( isset( $_GET['pngs'] ) && $_GET['pngs'] == 'yes' );
	if( !preg_match( "/^([a-z]{2,3}|)$/", $lang ) ){
		die( "Bad language name entered" );
	}

	$pngString = $includePNGs ? " OR il_to like '%png' OR il_to like '%PNG'" : "";
	if( $lang !== '' && $category !== '' ){
		// connect to database
		$mysqli = dbconnect( get_databasename( 'en', 'wikipedia' ) );
		$query = "select distinct p1.page_title from page p1 inner join categorylinks on cl_to='" . $mysqli->real_escape_string( str_replace( " ", "_", $category ) ) . "' and cl_from=p1.page_id and p1.page_namespace=1 inner join page p2 on p2.page_title=p1.page_title and p2.page_namespace=0 inner join imagelinks on (il_to like '%jpg' OR il_to like '%JPG'$pngString) and il_to not like '%icon%' and il_to not like '%stub%' and il_to not like '%flag%' and il_from=p2.page_id";
		if( $lang == "en" ){
			// Load blacklist line-by-line to keep memory down
			$handle = fopen( 'enblacklist.txt', 'r' ) or die( "can't open file" );
			// Top line as a header.
			$newfile = fgets( $handle );
			while( !feof( $handle ) ){
				$line = fgets( $handle );
				$line = str_replace( "'", "\'", $line );
				$query .= " and il_to != '" . trim( $line ) . "'";
			}
			fclose( $handle );
		}
		$result = $mysqli->query( $query ) or die( $mysqli->error . "<br /><br />$query" );
		$tagged = array();
		while( $row = $result->fetch_assoc() ){
			array_push( $tagged, str_replace( "_", " ", $row['page_title'] ) );
		}

		sort( $tagged );
	}
	echo get_html( 'header', 'Image existence checker' );
?>
	<p>Though this tool is designed for discovering situations where an article contains an image but still has its talk
		page tagged as requiring one, it can be used successfully for any 'Main article has a .jpg image but talk page
		is in category Y type requests'. It was written after a request by en Wikipedia user PC78 and the SQL was
		written by toolserver admin flyingparchment and then edited by me. Technically speaking, this tool assumes only
		.jpg (and, if selected, .png) images are qualifying works and that any such image (bar some limitations, see
		below) is indeed a qualifying work (even if its a 2x2 pixel template image, which are thankfully very rare).</p>
	<h3>Blacklisted</h3>
	<p>Files containg 'stub', 'flag' or 'icon' are automatically assumed to not be qualifying works. The English
		Wikipedia also has its own blacklist, which you can <a href="enblacklist.php">view and add to</a>. The scenario
		here would be that the tool lists works just because they have an image in their stub template, or something
		else like that. Add the name of that image to the watchlist.</p>
	<h3>Instructions</h3>
	<p>Just specify a language and a talk page cat (both cAse SenSItive) and press 'Go!'. The PHP is designed to give
		you a usable URL that you can give to others.</p>
	<form action="index.php" method="GET">
		<p><label for="lang">Site:&nbsp;</label>
			<input id="lang" type="text" name="lang" style="width: 50px" value="<?php echo $lang; ?>" required="required"/>.wikipedia.org
		</p>

		<p><label for="category">Talk page category:&nbsp;</label>
			<input type="text" id="category" name="category" style="width: 200px"
				value="<?php echo htmlspecialchars( $category ); ?>" required="required"/></p>
		<p><label for="pngs">Include PNGs as potential images:&nbsp;</label>
			<input type="checkbox" id="pngs" name="pngs"
				   value="yes" <?php if( $includePNGs ) echo 'checked="checked" '; ?>/></p>
		<input type="submit" value="Go!"/>
	</form>
<?php
	if( $lang != '' ){
		$count = count( $tagged );
		echo "<h3>Probably incorrectly tagged ($count in total):</h3>\n<ul>\n";
		foreach( $tagged as $article ) {
			$article = htmlspecialchars( $article );
			echo "<li><a href=\"http://" . $lang . ".wikipedia.org/wiki/$article\">$article</a> (";
			if( $lang == "en" ){
				echo "<a href=\"http://" . $lang . ".wikipedia.org/wiki/Talk:$article\">talk</a> | ";
			}
			echo "<a href=\"http://" . $lang . ".wikipedia.org/w/index.php?title=$article&action=edit\">edit</a>)</li>\n";
		}
		echo "</ul>\n";
	}
	echo get_html( 'footer' );