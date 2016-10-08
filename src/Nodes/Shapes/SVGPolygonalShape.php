<?php

namespace JangoBrick\SVG\Nodes\Shapes;

use JangoBrick\SVG\Nodes\SVGNode;
use JangoBrick\SVG\SVG;
use JangoBrick\SVG\SVGRenderingHelper;

abstract class SVGPolygonalShape extends SVGNode
{
    private $tagName;
    private $points;

    public function __construct($tagName, $points)
    {
        parent::__construct();

        $this->tagName = $tagName;
        $this->points = $points;
    }

    public function addPoint($a, $b = null)
    {
        if (!is_array($a)) {
            $a = array($a, $b);
        }

        $this->points[] = $a;
        return $this;
    }

    public function removePoint($index)
    {
        array_splice($this->points, $index, 1);
        return $this;
    }

    public function countPoints()
    {
        return count($this->points);
    }

    public function getPoints()
    {
        return $this->points;
    }

    public function getPoint($index)
    {
        return $this->points[$index];
    }

    public function setPoint($index, $point)
    {
        $this->points[$index] = $point;
        return $this;
    }

    public function toXMLString()
    {
        $s  = '<'.$this->tagName;

        $s .= ' points="';
        for ($i = 0, $n = count($this->points); $i < $n; ++$i) {
            $point = $this->points[$i];
            if ($i > 0) {
                $s .= ' ';
            }
            $s .= $point[0].','.$point[1];
        }
        $s .= '"';

        $this->addStylesToXMLString($s);
        $this->addAttributesToXMLString($s);

        $s .= ' />';

        return $s;
    }

    public function draw(SVGRenderingHelper $rh, $scaleX, $scaleY)
    {
        $rh->push();

        $opacity = $this->getStyle('opacity');
        if (isset($opacity) && is_numeric($opacity)) {
            $opacity = floatval($opacity);
            $rh->scaleOpacity($opacity);
        }

        // original (document fragment) width for unit parsing
        $ow = $rh->getWidth() / $scaleX;

        $p  = array();
        $np = count($this->points);

        for ($i = 0; $i < $np; ++$i) {
            $point = $this->points[$i];
            $p[]   = $point[0] * $scaleX;
            $p[]   = $point[1] * $scaleY;
        }

        $fill = $this->getComputedStyle('fill');
        if (isset($fill) && $fill !== 'none') {
            $fillColor = SVG::parseColor($fill, true);
            $rh->fillPolygon($p, $np, $fillColor);
        }

        $stroke = $this->getComputedStyle('stroke');
        if (isset($stroke) && $stroke !== 'none') {
            $strokeColor = SVG::parseColor($stroke, true);
            $rh->setStrokeWidth(SVG::convertUnit($this->getComputedStyle('stroke-width'), $ow) * $scaleX);
            $this->drawOutline($rh, $p, $np, $strokeColor);
        }

        $rh->pop();
    }

    abstract protected function drawOutline(SVGRenderingHelper $rh, $points, $numPoints, $strokeColor);
}