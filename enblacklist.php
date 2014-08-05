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

	// Load list of projects to check
	$images = file( 'enblacklist.txt' );
	$defaultnewitem = ( isset( $_POST['newitem'] ) ) ? $_POST["newitem"] : '';

	if( $defaultnewitem != '' ){
		if( preg_match( '/[>].*[<]/', $defaultnewitem ) ){
			die( "Sorry, that file name could not be added." );
		}
		$newitem = str_replace( ' ', '_', $defaultnewitem );
		$newitem = str_replace( 'File:', '', $newitem );
		$newitem = str_replace( 'Image:', '', $newitem );

		if( !in_array( $newitem, $images ) && $newitem != "" ){
			$handle = fopen( 'enblacklist.txt', 'a' ) or die( "can't open file" );
			fwrite( $handle, "\n" . $newitem );
			fclose( $handle );
		}
	}

	echo get_html( 'header', 'Image existence checker ' );
?>
	<p>This tool adds items to a en Wikipedia blacklist for the <a href="index.php">image checker tool</a> and <a
			href="../imagecheckerpage/index.php">image checker (page) tool</a>. Add items to the blacklist by typing
		them into the box and pressing 'Do it!'. Assume it's been added correctly. Any abuse of this tool will,
		unfortunately, lead to the process becoming much less open.</p>
	<form action="enblacklist.php" method="POST">
		<p><label for="newitem">Add file:</label>
			<input type="text" id="newitem" name="newitem" style="width: 200px"
			       value="<?php echo $defaultnewitem; ?>" required="required" /></p>
		<input type="submit" value="Do it!"/>
	</form>
	<h3>Existing blacklist</h3>
	<ul>
		<?php
			foreach( $images as $image ) {
				$image = htmlspecialchars( str_replace( "\'", "'", $image ) );
				echo "<li>$image</li>\n";
			}
		?>
	</ul>
<?php
	echo get_html( 'footer' );