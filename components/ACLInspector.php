<?php
/**
 * Created by Ascendro S.R.L.
 * User: Michael
 * Date: 14.08.13
 * Time: 14:01
 */
class ACLInspector extends CApplicationComponent
{
    /**
     * @param $relations array of aroObjects,acoObjects and connections.
     * @return string Returning the generated Graphviz Syntax
     */
    public function createGraphvizSyntax($relations) {
        $result = "";
        $result .= "digraph {\n";
        $result .= "  rankdir=\"LR\"\n";
        $result .= "  subgraph cluster_0 {\n";
        $result .= "    node[color=gold,style=filled];\n";
        foreach ($relations['aroObjects'] as $aro) {
            $id = $aro['model'].$aro['id'];
            $title = ($aro['alias'])?$aro['alias']:$id;
            $result .= "    $id [label=\"$title\"];\n";
        }
        $result .= "    label=\"ARO's\"\n";
        $result .= "  }\n";
        $result .= "  subgraph cluster_1 {\n";
        $result .= "    node[color=lightblue,style=filled];\n";
        $result .= "    edge[fontsize=10,color=darkgreen];\n";
        foreach ($relations['acoObjects'] as $aco) {
            $id = $aco['model'].$aco['id'];
            $title = ($aco['alias'])?$aco['alias']:$id;
            $result .= "    $id [label=\"$title\"];\n";
        }
        foreach ($relations['connections'] as $connection) {
            $result .= "    ".$connection['aro']." -> ".$connection['aco']." [label=\"".$connection['action']."\"];\n";
        }
        $result .= "    label=\"ACO's\"\n";
        $result .= "  }\n";
        $result .= "}\n";

        return $result;
	}

    /**
     * Get all permissions pathes between this two objects
     * @param aro - Access Request Object
     * @param aco - Access Control Object
     * @return array of nodes and relations
     */
    public function getAroAcoRelation($aro,$aco) {
        $acoObjects = array($aco);

        $aroObjects = array($aro);

        return $this->getRelations($acoObjects,$aroObjects);
    }

    /**
     * Get all objects who have permissions on this object
     * @param aro - Access Request Object
     * @return array of nodes and relations
     */
    public function getAroRelations($aro) {
        $acoClass = Strategy::getClass("Aco");
        $acoObjects = $acoClass::model()->findAll();

        $aroObjects = array($aro);

        return $this->getRelations($acoObjects,$aroObjects);
    }

    /**
     * Get all objects who have permissions on this object
     * @param aco - Access Control Object
     * @return array of nodes and relations
     */
    public function getAcoRelations($aco) {
        $acoObjects = array($aco);

        $aroClass = Strategy::getClass("Aro");
        $aroObjects = $aroClass::model()->findAll();

        return $this->getRelations($acoObjects,$aroObjects);
    }

    /**
     * Get all objects with all their permission relatoins
     * @return array of nodes and relations
     */
    public function getFullRelations() {
        $acoClass = Strategy::getClass("Aco");
        $acoObjects = $acoClass::model()->findAll();

        $aroClass = Strategy::getClass("Aro");
        $aroObjects = $aroClass::model()->findAll();

        return $this->getRelations($acoObjects,$aroObjects);
    }

    /**
     * Get all relations between the listed objects
     * @return array of nodes and relations
     */
    public function getRelations($acoObjects,$aroObjects) {
        $result = array(
            'aroObjects' => array(),
            'acoObjects' => array(),
            'connections' => array(),
        );

        $acoClass = Strategy::getClass("Aco");
        $aroClass = Strategy::getClass("Aro");

        $actions = $this->getActions();

        foreach ($aroObjects as $aro) {
            if (get_class($aro) == $aroClass) {
                $result['aroObjects'][] = array(
                    'id' => $aro->foreign_key,
                    'alias' => $aro->alias,
                    'model' => $aro->model,
                    'object' => $aro,
                );
            } else {
                $result['aroObjects'][] = array(
                    'id' => $aro->getPrimaryKey(),
                    'alias' => "",
                    'model' => get_class($aro),
                    'object' => $aro,
                );
            }
        }
        foreach ($acoObjects as $aco) {
            if (get_class($aco) == $acoClass) {
                $result['acoObjects'][] = array(
                    'id' => $aco->foreign_key,
                    'alias' => $aco->alias,
                    'model' => $aco->model,
                    'object' => $aco,
                );
            } else {
                $result['acoObjects'][] = array(
                    'id' => $aco->getPrimaryKey(),
                    'alias' => "",
                    'model' => get_class($aco),
                    'object' => $aco,
                );
            }
        }

        foreach ($result['aroObjects'] as $aro) {
            foreach ($result['acoObjects'] as $aco) {
                $permissions = array();

                foreach ($actions as $action) {
                    $connections = false;
                    try {
                        $connections = $aro['object']->may($aco['object'],$action);
                    } catch (Exception $e) {

                    }

                    if ($connections) {
                        $permissions[] = $action;
                    }
                }

                if (!empty($permissions)) {
                    $result['connections'][] = array(
                        'aco' => $aco['model'].$aco['id'],
                        'aro' => $aro['model'].$aro['id'],
                        'action' => implode(",",$permissions),
                    );
                }
            }
        }

        return $result;
    }

    protected function getActions() {
        $actionClass = Strategy::getClass("Action");
        $result['actions'] = $actionClass::model()->findAll();
        $actions = array();
        foreach($result['actions'] as $action) {
            $actions[] = $action->name;
        }
        return $actions;
    }

}
