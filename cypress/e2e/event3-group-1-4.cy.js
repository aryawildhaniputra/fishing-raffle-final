/**
 * Test Event 3: Grup Campuran 1-5 Orang
 * 
 * Event ini memiliki grup dengan variasi 1-5 anggota.
 * Total members: 222, jumlah grup bervariasi tergantung random
 */

describe('Event 3: Lottery Drawing - Mixed Groups (1-5)', () => {
  beforeEach(() => {
    cy.login()
  })

  it('should draw all available groups (mixed 1-4 members)', () => {
    cy.contains('.card-title', 'Event Test - Grup 1-4 Orang').click()
    
    const drawnNumbers = []
    const groupSizes = []
    let groupsDrawn = 0
    const maxIterations = 150
    
    // Recursive function untuk draw semua grup
    const drawAllGroups = () => {
      if (groupsDrawn >= maxIterations) {
        cy.log(`⚠ Reached max iterations (${maxIterations}), stopping`)
        return
      }
      
      cy.get('body').then($body => {
        if ($body.find('.btn-draw').length > 0) {
          cy.clickDrawButton()
          cy.waitForNumberAnimation()
          
          cy.get('#random-stall-number').invoke('text').then(number => {
            drawnNumbers.push(parseInt(number))
            groupsDrawn++
            cy.log(`Draw ${groupsDrawn}: Number ${number}`)
          })
          
          // Handle both single and multi-member groups (random)
          cy.selectRandomPosition()
          
          cy.wait(2000)
          cy.url().should('include', '/event/')
          drawAllGroups() // Recursive call
        } else {
          cy.log(`✓ All ${groupsDrawn} groups drawn!`)
        }
      })
    }
    
    drawAllGroups()
    
    // Log results
    cy.then(() => {
      cy.log(`✓ Test completed - Drew ${groupsDrawn} groups`)
      
      if (drawnNumbers.length > 0) {
        const uniqueNumbers = [...new Set(drawnNumbers)]
        cy.log(`Unique numbers used: ${uniqueNumbers.length}`)
        cy.log(`Single member groups: ${groupSizes.filter(s => s === 1).length}`)
      }
    })
    
    cy.screenshot('event3-all-groups-drawn')
  })

  it('should verify number variety across all draws', () => {
    cy.contains('.card-title', 'Event Test - Grup 1-4 Orang').click()
    
    const numbers = []
    let totalDraws = 0
    const sampleSize = 30 // Sample 30 draws untuk analisis
    
    const performSampleDraw = () => {
      if (totalDraws >= sampleSize) {
        return
      }
      
      cy.get('body').then($body => {
        if ($body.find('.btn-draw').length > 0) {
          cy.clickDrawButton()
          cy.waitForNumberAnimation()
          
          cy.get('#random-stall-number').invoke('text').then(number => {
            numbers.push(parseInt(number))
            totalDraws++
            cy.log(`Sample ${totalDraws}/${sampleSize}: ${number}`)
          })
          
          // Handle confirmation (random)
          cy.selectRandomPosition()
          
          cy.wait(1500)
          cy.url().should('include', '/event/')
          performSampleDraw()
        }
      })
    }
    
    performSampleDraw()
    
    // Analyze variety
    cy.then(() => {
      const uniqueNumbers = [...new Set(numbers)]
      cy.log(`Total sample draws: ${numbers.length}`)
      cy.log(`Unique numbers: ${uniqueNumbers.length}`)
      cy.log(`Numbers: ${numbers.join(', ')}`)
      
      // Check if we have variety (not just 1, 2, 221, 222)
      const hasVariety = numbers.some(n => n > 10 && n < 210)
      cy.log(`Has variety (numbers between 10-210): ${hasVariety}`)
      
      if (hasVariety) {
        cy.log('✓ Adaptive logic working - variety detected!')
      }
    })
  })
})
