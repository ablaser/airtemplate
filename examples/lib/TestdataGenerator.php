<?php

	class TestdataGenerator implements Iterator {
		protected $i;

		protected $max;

		protected $row;

		protected $valueAsArray;

		public function __construct($iterations=100, $valueAsArray=false) {
			$this->max = $iterations;
			$this->i = 0;
			$this->valueAsArray = $valueAsArray;
			$this->row = $this->generateRow();
		}

		public function rewind() {
			$this->i = 0;
			$this->row = $this->generateRow();
		}

		public function valid() {
			return $this->i < $this->max;
		}

		public function current() {
			return $this->row;
		}

		public function key() {
			return $this->i;
		}

		public function next() {
			if (false !== $this->row) {
				$this->i++;
				$this->row = $this->generateRow();
			}
		}

		public function __destruct() {
		}

		private function generateRow() {
			return array(
				'id' => $this->i + 1,
				'value' => $this->valueAsArray
					? [
						'a' => 'Value ' . ($this->i + 1) . '/a',
						'b' => 'Value ' . ($this->i + 1) . '/b'
					  ]
					: 'Value ' . ($this->i + 1),
				'desc' => '< Description ' . ($this->i + 1) . ' >',
			);
		}
	}

?>