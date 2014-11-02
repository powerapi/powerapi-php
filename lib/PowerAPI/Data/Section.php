<?php

namespace PowerAPI\Data;

/** Class used to hold section details.
 * @property array $assignments contains the section's assignments
 * @property array $expression section's expression
 * @property array $finalGrades final grades, grouped by term abbreviation
 * @property string $name section's name
 * @property string $roomName section's room name
 * @property array $teacher teacher's first and last name, email, and school phone
*/
class Section extends BaseObject
{
    /**
     * Parses the section details and populates the internal details store
     * @param array $details the details to be stored
     * @return void
     */
    public function __construct($details)
    {
        $this->details['assignments'] = $details['assignments'];

        $this->details['expression'] = $details['section']->expression;

        if ($details['finalGrades'] !== null) {
            $this->details['finalGrades'] = Array();

            foreach ($this->details['finalGrades'] as $finalGrade) {
                $this->details['finalGrades'][
                    $details['reportingTerms'][$finalGrade->reportingTermId]
                ] = $finalGrade->percent;
            }
        } else {
            $this->details['finalGrades'] = null;
        }

        $this->details['name'] = $details['section']->schoolCourseTitle;
        $this->details['roomName'] = $details['section']->roomName;
        $this->details['teacher'] = Array(
            'firstName' => $details['teacher']->firstName,
            'lastName' => $details['teacher']->lastName,
            'email' => $details['teacher']->email,
            'schoolPhone' => $details['teacher']->schoolPhone
        );
    }
}
