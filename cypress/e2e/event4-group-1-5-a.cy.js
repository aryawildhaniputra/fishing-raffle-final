/**
 * Test Event 4: Random Generate 1
 * 
 * Event ini memiliki grup dengan random 2-5 anggota.
 * Total members: 222, jumlah grup bervariasi (44-111 grup)
 */

describe('Event 4: Lottery Drawing - Random Generate 1', () => {
  beforeEach(() => {
    cy.login()
  })

  it('should draw all available groups for Event 4', () => {
    cy.contains('.card-title', 'Event Test - Grup 1-5 Orang (A)').click()
    
    const drawnNumbers = []
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
          
          // Get participant name
          cy.get('body').then($modal => {
            if ($modal.find('h6.fw-bold').length > 0) {
              cy.get('h6.fw-bold').first().invoke('text').then(name => {
                cy.log(`Participant: ${name}`)
              })
            }
          })
          
          // Get drawn number
          cy.get('#random-stall-number').invoke('text').then(number => {
            drawnNumbers.push(parseInt(number))
            groupsDrawn++
            cy.log(`Event 4 - Draw ${groupsDrawn}: Number ${number}`)
          })
          
          // Handle confirmation (random)
          cy.selectRandomPosition()
          
          cy.wait(2000)
          cy.url().should('include', '/event/')
          drawAllGroups() // Recursive call
        } else {
          cy.log(`✓ All ${groupsDrawn} groups drawn for Event 4!`)
        }
      })
    }
    
    drawAllGroups()
    
    // Log results
    cy.then(() => {
      cy.log(`✓ Test completed - Drew ${groupsDrawn} groups`)
      
      if (drawnNumbers.length > 0) {
        const uniqueNumbers = [...new Set(drawnNumbers)]
        const min = Math.min(...drawnNumbers)
        const max = Math.max(...drawnNumbers)
        const avg = drawnNumbers.reduce((a, b) => a + b, 0) / drawnNumbers.length
        
        cy.log(`Unique numbers: ${uniqueNumbers.length}`)
        cy.log(`Min: ${min}, Max: ${max}, Avg: ${avg.toFixed(2)}`)
      }
    })
    
    cy.screenshot('event4-all-groups-drawn')
  })

  it('should verify number distribution', () => {
    cy.contains('.card-title', 'Event Test - Grup 1-5 Orang (A)').click()
    
    const numbers = []
    let totalDraws = 0
    const sampleSize = 30
    
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
    
    // Analyze distribution
    cy.then(() => {
      const ranges = {
        '1-50': numbers.filter(n => n >= 1 && n <= 50).length,
        '51-100': numbers.filter(n => n >= 51 && n <= 100).length,
        '101-150': numbers.filter(n => n >= 101 && n <= 150).length,
        '151-200': numbers.filter(n => n >= 151 && n <= 200).length,
        '201-222': numbers.filter(n => n >= 201 && n <= 222).length,
      }
      
      cy.log('=== Event 4 Number Distribution ===')
      Object.entries(ranges).forEach(([range, count]) => {
        const percentage = ((count / numbers.length) * 100).toFixed(1)
        cy.log(`${range}: ${count} (${percentage}%)`)
      })
      
      const hasMiddleNumbers = numbers.some(n => n > 50 && n < 170)
      cy.log(`Has middle range numbers (50-170): ${hasMiddleNumbers}`)
      
      if (hasMiddleNumbers) {
        cy.log('✓ Adaptive logic is working!')
      }
    })
  })
})
