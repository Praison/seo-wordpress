<?php
/**
 * AISEO Enhanced Readability Analysis
 *
 * @package AISEO
 * @since 1.11.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enhanced Readability Analysis Class
 */
class AISEO_Readability {
    
    /**
     * Analyze readability comprehensively
     *
     * @param string $content Post content
     * @return array Readability analysis
     */
    public function analyze($content) {
        $clean_content = wp_strip_all_tags($content);
        
        return [
            'flesch_reading_ease' => $this->calculate_flesch_reading_ease($clean_content),
            'flesch_kincaid_grade' => $this->calculate_flesch_kincaid_grade($clean_content),
            'gunning_fog_index' => $this->calculate_gunning_fog($clean_content),
            'smog_index' => $this->calculate_smog($clean_content),
            'coleman_liau_index' => $this->calculate_coleman_liau($clean_content),
            'automated_readability_index' => $this->calculate_ari($clean_content),
            'passive_voice_percentage' => $this->calculate_passive_voice($clean_content),
            'transition_words_percentage' => $this->calculate_transition_words($clean_content),
            'sentence_variety' => $this->analyze_sentence_variety($clean_content),
            'paragraph_variety' => $this->analyze_paragraph_variety($content),
            'overall_score' => 0,
            'reading_level' => '',
            'recommendations' => []
        ];
    }
    
    /**
     * Calculate Flesch Reading Ease
     */
    private function calculate_flesch_reading_ease($content) {
        $words = str_word_count($content);
        $sentences = $this->count_sentences($content);
        $syllables = $this->count_syllables($content);
        
        if ($words == 0 || $sentences == 0) {
            return 0;
        }
        
        $score = 206.835 - 1.015 * ($words / $sentences) - 84.6 * ($syllables / $words);
        
        return round(max(0, min(100, $score)), 1);
    }
    
    /**
     * Calculate Flesch-Kincaid Grade Level
     */
    private function calculate_flesch_kincaid_grade($content) {
        $words = str_word_count($content);
        $sentences = $this->count_sentences($content);
        $syllables = $this->count_syllables($content);
        
        if ($words == 0 || $sentences == 0) {
            return 0;
        }
        
        $grade = 0.39 * ($words / $sentences) + 11.8 * ($syllables / $words) - 15.59;
        
        return round(max(0, $grade), 1);
    }
    
    /**
     * Calculate Gunning Fog Index
     */
    private function calculate_gunning_fog($content) {
        $words = str_word_count($content);
        $sentences = $this->count_sentences($content);
        $complex_words = $this->count_complex_words($content);
        
        if ($words == 0 || $sentences == 0) {
            return 0;
        }
        
        $fog = 0.4 * (($words / $sentences) + 100 * ($complex_words / $words));
        
        return round(max(0, $fog), 1);
    }
    
    /**
     * Calculate SMOG Index
     */
    private function calculate_smog($content) {
        $sentences = $this->count_sentences($content);
        $polysyllables = $this->count_polysyllables($content);
        
        if ($sentences == 0) {
            return 0;
        }
        
        $smog = 1.043 * sqrt($polysyllables * (30 / $sentences)) + 3.1291;
        
        return round(max(0, $smog), 1);
    }
    
    /**
     * Calculate Coleman-Liau Index
     */
    private function calculate_coleman_liau($content) {
        $words = str_word_count($content);
        $sentences = $this->count_sentences($content);
        $characters = strlen(preg_replace('/\s+/', '', $content));
        
        if ($words == 0) {
            return 0;
        }
        
        $L = ($characters / $words) * 100;
        $S = ($sentences / $words) * 100;
        
        $cli = 0.0588 * $L - 0.296 * $S - 15.8;
        
        return round(max(0, $cli), 1);
    }
    
    /**
     * Calculate Automated Readability Index
     */
    private function calculate_ari($content) {
        $words = str_word_count($content);
        $sentences = $this->count_sentences($content);
        $characters = strlen(preg_replace('/\s+/', '', $content));
        
        if ($words == 0 || $sentences == 0) {
            return 0;
        }
        
        $ari = 4.71 * ($characters / $words) + 0.5 * ($words / $sentences) - 21.43;
        
        return round(max(0, $ari), 1);
    }
    
    /**
     * Calculate passive voice percentage
     */
    private function calculate_passive_voice($content) {
        $sentences = preg_split('/[.!?]+/', $content, -1, PREG_SPLIT_NO_EMPTY);
        $passive_count = 0;
        
        $passive_indicators = ['is', 'are', 'was', 'were', 'been', 'being', 'be'];
        $past_participle_endings = ['ed', 'en', 'ne', 'wn'];
        
        foreach ($sentences as $sentence) {
            $words = str_word_count(strtolower($sentence), 1);
            
            foreach ($passive_indicators as $indicator) {
                if (in_array($indicator, $words)) {
                    foreach ($past_participle_endings as $ending) {
                        if (preg_match('/\b\w+' . $ending . '\b/', strtolower($sentence))) {
                            $passive_count++;
                            break 2;
                        }
                    }
                }
            }
        }
        
        $total_sentences = count($sentences);
        return $total_sentences > 0 ? round(($passive_count / $total_sentences) * 100, 1) : 0;
    }
    
    /**
     * Calculate transition words percentage
     */
    private function calculate_transition_words($content) {
        $transition_words = [
            'however', 'therefore', 'furthermore', 'moreover', 'consequently',
            'nevertheless', 'additionally', 'meanwhile', 'similarly', 'likewise',
            'instead', 'otherwise', 'subsequently', 'accordingly', 'hence',
            'thus', 'also', 'besides', 'indeed', 'furthermore', 'finally'
        ];
        
        $sentences = preg_split('/[.!?]+/', $content, -1, PREG_SPLIT_NO_EMPTY);
        $transition_count = 0;
        
        foreach ($sentences as $sentence) {
            $words = str_word_count(strtolower($sentence), 1);
            
            foreach ($transition_words as $transition) {
                if (in_array($transition, $words)) {
                    $transition_count++;
                    break;
                }
            }
        }
        
        $total_sentences = count($sentences);
        return $total_sentences > 0 ? round(($transition_count / $total_sentences) * 100, 1) : 0;
    }
    
    /**
     * Analyze sentence variety
     */
    private function analyze_sentence_variety($content) {
        $sentences = preg_split('/[.!?]+/', $content, -1, PREG_SPLIT_NO_EMPTY);
        $lengths = [];
        
        foreach ($sentences as $sentence) {
            $lengths[] = str_word_count($sentence);
        }
        
        if (empty($lengths)) {
            return ['variance' => 0, 'score' => 0];
        }
        
        $mean = array_sum($lengths) / count($lengths);
        $variance = 0;
        
        foreach ($lengths as $length) {
            $variance += pow($length - $mean, 2);
        }
        
        $variance = $variance / count($lengths);
        $score = min(100, $variance * 2);
        
        return [
            'variance' => round($variance, 2),
            'score' => round($score),
            'avg_length' => round($mean, 1)
        ];
    }
    
    /**
     * Analyze paragraph variety
     */
    private function analyze_paragraph_variety($content) {
        $paragraphs = array_filter(explode("\n\n", $content));
        $lengths = [];
        
        foreach ($paragraphs as $paragraph) {
            $lengths[] = str_word_count(wp_strip_all_tags($paragraph));
        }
        
        if (empty($lengths)) {
            return ['variance' => 0, 'score' => 0];
        }
        
        $mean = array_sum($lengths) / count($lengths);
        $variance = 0;
        
        foreach ($lengths as $length) {
            $variance += pow($length - $mean, 2);
        }
        
        $variance = $variance / count($lengths);
        $score = min(100, $variance / 10);
        
        return [
            'variance' => round($variance, 2),
            'score' => round($score),
            'avg_length' => round($mean, 1)
        ];
    }
    
    /**
     * Count sentences
     */
    private function count_sentences($content) {
        return count(preg_split('/[.!?]+/', $content, -1, PREG_SPLIT_NO_EMPTY));
    }
    
    /**
     * Count syllables
     */
    private function count_syllables($content) {
        $words = str_word_count(strtolower($content), 1);
        $total = 0;
        
        foreach ($words as $word) {
            $total += $this->count_word_syllables($word);
        }
        
        return $total;
    }
    
    /**
     * Count syllables in a word
     */
    private function count_word_syllables($word) {
        $word = strtolower($word);
        $syllables = 0;
        $vowels = ['a', 'e', 'i', 'o', 'u', 'y'];
        $previous_was_vowel = false;
        
        for ($i = 0; $i < strlen($word); $i++) {
            $is_vowel = in_array($word[$i], $vowels);
            
            if ($is_vowel && !$previous_was_vowel) {
                $syllables++;
            }
            
            $previous_was_vowel = $is_vowel;
        }
        
        // Adjust for silent e
        if (substr($word, -1) === 'e') {
            $syllables--;
        }
        
        return max(1, $syllables);
    }
    
    /**
     * Count complex words (3+ syllables)
     */
    private function count_complex_words($content) {
        $words = str_word_count(strtolower($content), 1);
        $complex = 0;
        
        foreach ($words as $word) {
            if ($this->count_word_syllables($word) >= 3) {
                $complex++;
            }
        }
        
        return $complex;
    }
    
    /**
     * Count polysyllables (3+ syllables)
     */
    private function count_polysyllables($content) {
        return $this->count_complex_words($content);
    }
}
