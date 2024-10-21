<?php
session_start();
if(!(isset($_SESSION['username']))){
    header("location: index.php");
}
elseif($_SESSION['usertype']!="admin" && $_SESSION['usertype']!="student" && $_SESSION['usertype']!="mentor"){
    header("location: index.php");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MAP - Project Guidelines</title>
    <link href="https://unpkg.com/tailwindcss@^2.0/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        header h1, footer p {
            font-family: 'Roboto', sans-serif;
        }
        main {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-top: -3rem;
            z-index: 10;
            position: relative;
        }
        section {
            margin-bottom: 1.5rem;
        }
        section h3 {
            font-family: 'Roboto', sans-serif;
            border-bottom: 2px solid #4b6cb7;
            padding-bottom: 0.5rem;
            margin-bottom: 1rem;
        }
        ul li {
            margin-bottom: 0.5rem;
        }
        footer {
            border-top: 2px solid #4b6cb7;
        }
        @media (max-width: 768px) {
            main {
                padding: 1rem;
            }
            h2, h3 {
                font-size: 1.25rem;
            }
            p, ul li {
                font-size: 0.875rem;
            }
            table th, table td {
                font-size: 0.875rem;
                padding: 0.5rem;
            }
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-800">

<?php 
if($_SESSION['usertype'] == "admin"){
    include 'adminheaders.php';
}
elseif($_SESSION['usertype'] == "student"){
    include 'studentheaders.php';
}
elseif($_SESSION['usertype'] == "mentor"){
    include 'mentorheaders.php';
}
?>
    <?php include 'footer.php' ?>

    <main class="max-w-4xl mx-auto p-4 bg-white shadow-md rounded-lg mt-8">
        <section class="mb-4">
        <center><h2 class="text-2xl font-bold mb-4">Project Guidelines</h2></center>
        <h2 class="text-xl font-semibold mb-2">"The final year B. Tech. project is defined as the development of a model or application (software or hardware) useful in exploring and/or solving an engineering problem".</h2>
            <p class="text-justify">These are guidelines for successful completion of the B. Tech. Projects in effective and uniform conduction of projects to be carried out by undergraduate B. Tech. Students in Semester VII and Semester VIII. It is expected that these guidelines will help in the overall improvement in the quality of B. Tech. Projects along with improvement in the evaluation process. The B. Tech. Project is a partial requirement for the successful completion of the degree. It can be two types: Projects based on implementing any application-oriented problem, which will be more or less experimental. The others will be based on some innovative (research-oriented) theoretical work.</p>
            <p class="text-justify">Department Evaluation Committee (DEC) is created to monitor the overall functioning of the activities related to the B. Tech. projects and to have an academic bridge among the various groups.</p>
        </section>

        <section class="mb-4">
            <h3 class="text-lg font-semibold mb-2">Roles of Department Evaluation Committee (DEC):</h3>
            <ul class="list-disc pl-5">
                <li class="mb-2">This Committee can prepare a project calendar mentioning the dates of project activities and evaluation dates.</li>
                <li class="mb-2">This Committee will be responsible for evaluating the timely progress of the projects and communicating the progress report to the students.</li>
                <li class="mb-2">At the end of the odd semester third-year (V semester), the Department Evaluation Committee should float the list of projects offered by the department or project proposal from students.</li>
                <li class="mb-2">If the DEC observed that any group of students is not performing well, this Committee should take special care to improve their performance through counseling them.</li>
                <li class="mb-2">DEC can decide project evaluation rubrics.</li>
            </ul>
        </section>

        <section class="mb-4">
            <h3 class="text-lg font-semibold mb-2">The role of the supervisor:</h3>
            <ul class="list-disc pl-5">
                <li class="mb-2">By the middle of the third year, even Semester (VI semester), the supervisor will send the detailed information about the projects to be offered by them to the Department Evaluation Committee.</li>
                <li class="mb-2">The supervisor must regularly monitor the progress being carried out by the project groups. If it is found that progress is unsatisfactory, it should be reported to the Department Evaluation Committee for necessary action.</li>
                <li class="mb-2">The supervisor is expected to look into the project report for the desired format before the final submission.</li>
            </ul>
        </section>

        <section>
            <h3 class="text-lg font-semibold mb-2">Instructions for students:</h3>
            <ul class="list-disc pl-5">
                <li class="mb-2">A project group of a minimum of 1 and a maximum of 5 can be formed by students, or DEC. Project members should be from the same course to work on their project at the beginning of VI Semester.</li>
                <li class="mb-2">After forming the project group, DEC will allocate supervisor/s to each project.</li>
                <li class="mb-2">Students have to select the project area and report to the concerned supervisor with the idea of project work they want to do within 15 days.</li>
                <li class="mb-2">A list of final supervisor allotments approved by DEC is floated to students.</li>
                <li class="mb-2">During the synopsis presentation, the project can be accepted/rejected. DEC will take this decision. If rejected, the group must develop a new project idea within 7 days of the project being rejected.</li>
                <li class="mb-2">The group must report to their supervisor twice a week and show/update them with the progress of their work.</li>
                <li class="mb-2">The group must maintain a record of their meetings along with remarks of their discussion and their supervisor's signature. This record is to be shown in front of DEC when the supervisor schedules the internal project presentation.</li>
                <li class="mb-2">A research paper must be accepted/published in a conference/journal related to the project and need to be shown at the final internal project presentation.</li>
            </ul>
        </section>

        <section class="mb-4">
            <h3 class="text-lg font-semibold mb-2">Evaluation Procedure:</h3>
            <p class="text-justify">To ensure proper conduction of each project, progress of each project should be monitored on a continuous basis, first by the supervisor and then by the DEC. In order to do so, it is planned to hold four presentations to be made by each project group in each Semester.</p>
            
            <h4 class="text-md font-semibold mb-2">First presentation:</h4>
            <p class="text-justify">The first presentation will be purely for approval of the project proposal presentation, which DEC will take in the first week of the VII semester. The project proposal is considered to be approved if it is passed in this presentation. If the presentation is not up to the mark, either the Committee will ask the students and their supervisor to modify the project slightly within a week and present again or change the project (in case the Committee finds the project not of sufficient standard or not feasible). In this presentation, the DEC is supposed to mark each student/group based on their project proposal content, presentation made, queries answered, and attendance out of 18 marks (Evaluation is performed according to Project Evaluation Rubrics) and send it to DEC.</p>
            
            <h4 class="text-md font-semibold mb-2">Second presentation:</h4>
            <p class="text-justify">The second presentation will be a purely synopsis presentation, which will be taken by an internal examiner appointed by DEC and scheduled by the supervisor. The project is assumed to be already selected by the students. In this presentation, they are required to show a brief presentation describing the main Aim/Objective of the project, division of objective into sub-objective, the methodology used, and implementation, which they will be pursuing. If the internal examiner is not satisfied with the presentation or feels that the project is not up to the mark, the examiner can reject the project. In this presentation, the DEC is supposed to mark each student/group out of 30 marks (Evaluation is performed according to Project Evaluation Rubrics). The DEC will then send it to DEC.</p>
        </section>

        <section class="mb-4">
        <center><h2 class="text-2xl font-bold mb-4">Rubrics Review</h2></center>
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="border px-4 py-2">Review #</th>
                        <th class="border px-4 py-2">Agenda</th>
                        <th class="border px-4 py-2">Assessment</th>
                        <th class="border px-4 py-2">Last Date</th>
                        <th class="border px-4 py-2">Review Assessment Weightage</th>
                        <th class="border px-4 py-2">Overall Weightage</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="hover:bg-blue-50">
                        <td class="border px-4 py-2">Review 1</td>
                        <td class="border px-4 py-2">Project Proposal Evaluation</td>
                        <td class="border px-4 py-2">Rubric R1</td>
                        <td class="border px-4 py-2">March 15, 2023</td>
                        <td class="border px-4 py-2">(18)</td>
                        <td class="border px-4 py-2" rowspan="3">50</td>
                    </tr>
                    <tr class="hover:bg-blue-50">
                        <td class="border px-4 py-2">Review 2</td>
                        <td class="border px-4 py-2">Project Synopsis</td>
                        <td class="border px-4 py-2">Rubric R2</td>
                        <td class="border px-4 py-2">May 30, 2023</td>
                        <td class="border px-4 py-2">(24)</td>
                    </tr>
                    <tr class="hover:bg-blue-50">
                        <td class="border px-4 py-2">Review 3</td>
                        <td class="border px-4 py-2">Evaluation by Supervisor</td>
                        <td class="border px-4 py-2">Rubric R3</td>
                        <td class="border px-4 py-2">July 25, 2023</td>
                        <td class="border px-4 py-2">(8)</td>
                    </tr>
                    <tr class="hover:bg-blue-50">
                        <td class="border px-4 py-2">Review 4</td>
                        <td class="border px-4 py-2">7th Semester Project Evaluation</td>
                        <td class="border px-4 py-2">Rubric R4</td>
                        <td class="border px-4 py-2">August 30, 2023 *</td>
                        <td class="border px-4 py-2">(100)</td>
                        <td class="border px-4 py-2">100</td>
                    </tr>
                    <tr class="hover:bg-blue-50">
                        <td class="border px-4 py-2">Review 5</td>
                        <td class="border px-4 py-2">8th Semester Project Evaluation</td>
                        <td class="border px-4 py-2">Rubric R5</td>
                        <td class="border px-4 py-2">November 15, 2023</td>
                        <td class="border px-4 py-2">(50(I)+100(E)=150)</td>
                        <td class="border px-4 py-2" rowspan="4">400</td>
                    </tr>
                    <tr class="hover:bg-blue-50">
                        <td class="border px-4 py-2">Review 6</td>
                        <td class="border px-4 py-2">Project Report Evaluation</td>
                        <td class="border px-4 py-2">Rubric R6</td>
                        <td class="border px-4 py-2">December 30, 2023</td>
                        <td class="border px-4 py-2">(30(I)+60(E)=90)</td>
                    </tr>
                    <tr class="hover:bg-blue-50">
                        <td class="border px-4 py-2">Review 7</td>
                        <td class="border px-4 py-2">Evaluation by Department Project Coordinator</td>
                        <td class="border px-4 py-2">Rubric R7</td>
                        <td class="border px-4 py-2">March 30, 2024</td>
                        <td class="border px-4 py-2">(20(I)+50(E)=70)</td>
                    </tr>
                    <tr class="hover:bg-blue-50">
                        <td class="border px-4 py-2">Review 8</td>
                        <td class="border px-4 py-2">Evaluation by Supervisor</td>
                        <td class="border px-4 py-2">Rubric R8</td>
                        <td class="border px-4 py-2">April 6, 2024</td>
                        <td class="border px-4 py-2">90</td>
                    </tr>
                    <tr>
                        <td colspan="5" class="border px-4 py-2 font-bold">Total</td>
                        <td class="border px-4 py-2 font-bold">550</td>
                    </tr>
                </tbody>
            </table>
        </div>
        </section>
    </main>

</body>
</html>
