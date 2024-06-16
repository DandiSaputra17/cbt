<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CourseQuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Course $course)
    {
        //
        $students = $course->students()->orderBy('id', 'DESC')->get();

        return view('admin.questions.create', [
            'course' => $course,
            'students' => $students,

        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Course $course)
    {
        //
        $validated = $request->validate([
            'question' => 'required|string|max:255',
            'answers' => 'required|array',
            'answers.*' => 'required|string',
            'correct_answer' => 'required|integer',
        ]);

        DB::beginTransaction();

        try {
            $question = $course->questions()->create([
                'question' => $request->question,
            ]);

            foreach($request->answers as $index => $answerText) {
                $isCorrect = ($request->correct_answer == $index);
                $question->answers()->create([
                    'answer' => $answerText,
                    'is_correct' => $isCorrect,
                ]);
            }

            DB::commit();

            return redirect()->route('dashboard.courses.show', $course->id);
        }

        catch(\Exception $e) {
            DB::rollback();
            $error = ValidationException::withMessages([
                'system_error' => ['System Error!' . $e->getMessage()],
            ]);
            throw $error;
        }

    }

    /**
     * Display the specified resource.
     */
    public function show(CourseQuestion $courseQuestion)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CourseQuestion $courseQuestion)
    {
        //
        $course = $courseQuestion->course;
        $students = $course->students()->orderBy('id', 'DESC')->get();
        return view('admin.questions.edit', [
            'courseQuestion' => $courseQuestion,
            'course' => $course,
            'students' => $students,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CourseQuestion $courseQuestion)
    {
        //
        $validated = $request->validate([
            'question' => 'required|string|max:255',
            'answers' => 'required|array',
            'answers.*' => 'required|string',
            'correct_answer' => 'required|integer',
        ]);

        DB::beginTransaction();

        try {
            $courseQuestion->update([
                'question' => $request->question,
            ]);

            $courseQuestion->answers()->delete();

            foreach($request->answers as $index => $answerText) {
                $isCorrect = ($request->correct_answer == $index);
                $courseQuestion->answers()->create([
                    'answer' => $answerText,
                    'is_correct' => $isCorrect,
                ]);
            }

            DB::commit();

            return redirect()->route('dashboard.courses.show', $courseQuestion->course_id);
        }

        catch(\Exception $e) {
            DB::rollback();
            $error = ValidationException::withMessages([
                'system_error' => ['System Error!' . $e->getMessage()],
            ]);
            throw $error;
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CourseQuestion $courseQuestion)
    {
        //
        try{
            $courseQuestion->delete();
            return redirect()->route('dashboard.courses.show', $courseQuestion->course_id);
        }
        catch(\Exception $e) {
            DB::rollback();
            $error = ValidationException::withMessages([
                'system_error' => ['System Error!' . $e->getMessage()],
            ]);
            throw $error;
        }
    }
}
