<?php
/**
 * Script to update Pygments CSS
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 * @author Ori Livneh <ori@wikimedia.org>
 * @ingroup Maintenance
 */

use Symfony\Component\Process\ProcessBuilder;

$IP = getenv( 'MW_INSTALL_PATH' ) ?: __DIR__ . '/../../..';

require_once "$IP/maintenance/Maintenance.php";

class UpdateCSS extends Maintenance {

	public function __construct() {
		parent::__construct();
		$this->addDescription( 'Generate CSS code for SyntaxHighlight_GeSHi' );
	}

	public function execute() {
		$target = __DIR__ . '/../modules/pygments.generated.css';
		$css = "/* Stylesheet generated by updateCSS.php */\n";

		$builder = new ProcessBuilder();
		$builder->setPrefix( SyntaxHighlight_GeSHi::getPygmentizePath() );

		$process = $builder
			->add( '-f' )->add( 'html' )
			->add( '-S' )->add( 'default' )
			->add( '-a' )->add( '.' . SyntaxHighlight::HIGHLIGHT_CSS_CLASS )
			->getProcess();

		$process->run();

		if ( !$process->isSuccessful() ) {
			throw new \RuntimeException( $process->getErrorOutput() );
		}

		$css .= $process->getOutput();

		if ( file_put_contents( $target, $css ) === false ) {
			$this->output( "Failed to write to {$target}\n" );
		} else {
			$this->output( 'CSS written to ' . realpath( $target ) . "\n" );
		}
	}
}

$maintClass = "UpdateCSS";
require_once RUN_MAINTENANCE_IF_MAIN;
